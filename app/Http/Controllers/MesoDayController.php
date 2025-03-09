<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use Inertia\Inertia;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Support\Facades\DB;

class MesoDayController extends Controller
{
    public function show(Mesocycle $mesocycle, MesoDay $day): \Inertia\Response
    {
        $mesocycle->load('days:id,mesocycle_id,label');


        // DayExercise::whereIn('exercise_id', $day->)

        // ExerciseSet::whereHas('day_exercise_id', function(Builder $query) {
        //     $query->whereIn('meso_day_id', $mesocycle->days->pluck('id'))
        // })->where('meso_day_id');

        $day->load(['dayExercises' => ['exercise' => ['muscleGroup'], 'sets']]);

        $exerciseIds = $day->dayExercises()->pluck('exercise_id');
        $daysIds = $mesocycle->days->pluck('id');

        // $ex = DayExercise::whereIn('meso_day_id', $daysIds)
        //                 ->whereIn('exercise_id', $exerciseIds)
        //                 ->where('meso_day_id', '<', $day->id)
        //                 ->orderBy('id', 'DESC')
        //                 ->with('sets')
        //                 ->get();

        // $test = Db::table('meso_days')->whereIn('id', [1, 2])->
        $test = DB::table('day_exercises')->whereIn('meso_day_id', $daysIds)->selectRaw('MAX(id)')->groupBy('meso_day_id')->get();
        dd($test);


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
        })
        ->with(['sets' => function ($query) {
            $query->whereNotNull('weight')->whereNotNull('reps'); // Only load valid sets
        }])
        ->orderBy('id', 'DESC')
        ->get();

                        // dd($latestDayExercises);
        
        $targetValues = [];

        foreach ($latestDayExercises as $dayExercise) {
            foreach ($dayExercise->sets as $set) {
                $targetValues[$dayExercise->exercise_id]['reps'][] = $set->reps;
                $targetValues[$dayExercise->exercise_id]['weight'][] = $set->weight;
            }
        }

        dd($targetValues);

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

        // dd($mesocycle);

        return Inertia::render('mesocycles/show', ['mesocycle' => $mesocycle]);
    }
}
