<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Inertia\Inertia;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;

class MesoDayController extends Controller
{

    public function show(int $mesocycle, MesoDay $day): \Inertia\Response
    {

        $day->load(['dayExercises' => function ($query) {
            $query->orderBy('position');
        }, 'dayExercises.exercise.muscleGroup', 'dayExercises.sets']);

        Gate::authorize('view', $day);

        $mesocycle = $day->mesocycle;

        $exerciseIds = $day->dayExercises()->pluck('exercise_id');
        $daysIds = $mesocycle->days->pluck('id');

        // Get only the latest `DayExercise` per `exercise_id` where `weight` and `reps` are NOT NULL
        $latestDayExercises = DayExercise::whereIn('id', function ($query) use ($exerciseIds, $daysIds, $day) {
            $query->selectRaw('MAX(id)')
                ->from('day_exercises')
                ->whereIn('exercise_id', $exerciseIds)
                ->whereIn('meso_day_id', $daysIds)
                ->where('meso_day_id', '<', $day->id)
                ->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('exercise_sets')
                        ->whereRaw('exercise_sets.day_exercise_id = day_exercises.id')
                        ->whereNotNull('exercise_sets.weight')
                        ->whereNotNull('exercise_sets.reps'); // Ensuring valid data
                })
                ->groupBy('exercise_id');
        })->with(['sets' => function ($query) {
            $query->whereNotNull('weight')->whereNotNull('reps'); // Only load valid sets
        }])
            ->orderBy('id', 'DESC')
            ->get()
            ->keyBy('exercise_id');

        // days =>
        // days -> sets (sets / exercise)
        // days -> sets (sets / exercise)

        // day => 
        // exercise in a day -> sets
        // exercise in a day -> sets

        $lastSets = [];

        foreach ($latestDayExercises as $dayExercise) {
            $lastSets[$dayExercise->exercise_id] = $dayExercise->sets;
        }

        foreach ($day->dayExercises as $dayExercise) {
            if (isset($lastSets[$dayExercise->exercise_id])) {
                for ($i = 0; $i < count($dayExercise->sets); $i++) {
                    // Create set entry if it does not exist
                    if (! $dayExercise->sets[$i]) {
                        $dayExercise->sets[$i]->day_exercise_id = $dayExercise->id;

                        $dayExercise->sets[$i]->target_reps     = $lastSets[$i]->reps;
                        $dayExercise->sets[$i]->target_weight   = $lastSets[$i]->weight;
                    } elseif (isset($lastSets[$dayExercise->exercise_id][$i])) {
                        $dayExercise->sets[$i]->target_reps     = $lastSets[$dayExercise->exercise_id][$i]->reps;
                        $dayExercise->sets[$i]->target_weight   = $lastSets[$dayExercise->exercise_id][$i]->weight;
                    }
                }
            }
        }

        $calendar = [];
        $weekIdx = 1;

        foreach ($mesocycle->days as $idx => $d) {
            $calendar[$weekIdx][] = $d;

            if (count($calendar[$weekIdx]) == $mesocycle->days_per_week) {
                $weekIdx++;
            }
        }

        $mesocycle->calendar = $calendar;

        $mesocycle->setRelation('day', $day);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }


    public function toggleDay(MesoDay $day): RedirectResponse
    {
        Gate::authorize('owns', $day);

        if (! $day->canFinish()) {
            throw new AppException(422, __('All sets must be completed'), 'SETS_UNFINISHED');
        }

        $day->finished_at = $day->finished_at ? null : now();

        $day->save();

        return redirect()->back()->with('day', $day->finished_at);
    }
}
