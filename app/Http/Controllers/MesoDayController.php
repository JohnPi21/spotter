<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Models\DayExercise;
use Inertia\Inertia;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Actions\Mesocycle\MakeCalendarAction as MesocycleMakeCalendarAction;

class MesoDayController extends Controller
{
    public function show(int $mesocycle, MesoDay $day, MesocycleMakeCalendarAction $makeCalendarAction): \Inertia\Response
    {

        $day->load(['dayExercises' => fn($q) => $q->orderBy('position'), 'dayExercises.exercise.muscleGroup', 'dayExercises.sets', 'mesocycle']);

        Gate::authorize('view', $day);

        $mesocycle = $day->mesocycle;

        // CREATE OBSERVER for ExerciseSet

        // $exerciseIds = $day->dayExercises()->pluck('exercise_id');
        // $daysIds = $mesocycle->days->pluck('id');

        // Take the exercise from the same day last week instead
        // On set creation / delete / update -> dispatch an event that updates target reps / weight next week same day
        // Simplify this by getting only the current day exercises

        // $latestDayExercises = DayExercise::whereIn('id', function ($query) use ($exerciseIds, $daysIds, $day) {
        //     $query->selectRaw('MAX(id)')
        //         ->from('day_exercises')
        //         ->whereIn('exercise_id', $exerciseIds)
        //         ->whereIn('meso_day_id', $daysIds)
        //         ->where('meso_day_id', '<', $day->id)
        //         ->whereExists(function ($subQuery) {
        //             $subQuery->select(DB::raw(1))
        //                 ->from('exercise_sets')
        //                 ->whereRaw('exercise_sets.day_exercise_id = day_exercises.id')
        //                 ->whereNotNull('exercise_sets.weight')
        //                 ->whereNotNull('exercise_sets.reps'); // Ensuring valid data
        //         })
        //         ->groupBy('exercise_id');
        // })->with(['sets' => function ($query) {
        //     $query->whereNotNull('weight')->whereNotNull('reps'); // Only load valid sets
        // }])
        //     ->orderBy('id', 'DESC')
        //     ->get()
        //     ->keyBy('exercise_id');


        // $lastSets = [];

        // foreach ($latestDayExercises as $dayExercise) {
        //     $lastSets[$dayExercise->exercise_id] = $dayExercise->sets;
        // }

        // foreach ($day->dayExercises as $dayExercise) {
        //     if (isset($lastSets[$dayExercise->exercise_id])) {
        //         for ($i = 0; $i < count($dayExercise->sets); $i++) {
        //             // Create set entry if it does not exist
        //             if (! $dayExercise->sets[$i]) {
        //                 $dayExercise->sets[$i]->day_exercise_id = $dayExercise->id;

        //                 $dayExercise->sets[$i]->target_reps     = $lastSets[$i]->reps;
        //                 $dayExercise->sets[$i]->target_weight   = $lastSets[$i]->weight;
        //             } elseif (isset($lastSets[$dayExercise->exercise_id][$i])) {
        //                 $dayExercise->sets[$i]->target_reps     = $lastSets[$dayExercise->exercise_id][$i]->reps;
        //                 $dayExercise->sets[$i]->target_weight   = $lastSets[$dayExercise->exercise_id][$i]->weight;
        //             }
        //         }
        //     }
        // }

        $calendar = $makeCalendarAction($mesocycle);

        $mesocycle->calendar = $calendar;
        $mesocycle->makeHidden('days');
        $day->makeHidden('mesocycle');

        $mesocycle->setRelation('day', $day);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }


    public function toggleDay(MesoDay $day): RedirectResponse
    {
        Gate::authorize('owns', $day);

        if (! $day->canFinish()) {
            throw new AppException(422, __('All sets must be completed'), 'SETS_UNFINISHED');
        }

        $day->update(['finished_at' => $day->finished_at ? null : now()]);

        return redirect()->back()->with('day', $day->finished_at);
    }
}
