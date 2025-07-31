<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $request->validate([
            'displayBy' => ['string', Rule::in(['weight', 'volume']), 'sometimes'],
        ]);

        $displayBy = $request->query('displayBy', 'weight');

        $selectorBy = $displayBy === 'weight' ? "weight" : "reps * weight";

        $bestLifts = ExerciseSet::query()
            ->selectRaw("day_exercise_id, MAX($selectorBy) as best_value")
            ->forUser(Auth::id())
            ->groupBy('day_exercise_id')
            ->orderByDesc('best_value')
            ->limit(3)
            ->with('dayExercise.exercise')
            ->get();

        $mappedLifts = $bestLifts->map(function ($exerciseSet) use ($displayBy) {
            return [
                'exercise' => $exerciseSet->dayExercise->exercise->name,
                'value'    => $exerciseSet->best_value,
                'unit'     => $displayBy === 'weight' ? 'kg' : 'kg x reps'
            ];
        });

        $activity = [];

        $lastMesoDays = MesoDay::with(['mesocycle', 'dayExercises.exercise.muscleGroup', 'dayExercises.sets'])
            ->forUser(Auth::id())
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $lastWorkouts = $lastMesoDays->map(function ($mesoDay) {
            $finishDate = Carbon::parse($mesoDay->updated_at);
            $setsCount = $mesoDay->dayExercises->sum(fn($dayExercise) => $dayExercise->sets->count());

            $totalVolume = 0;
            $mesoDay->dayExercises->each(function ($dayExercise) use (&$totalVolume) {
                $dayExercise->sets->each(function ($set) use (&$totalVolume) {
                    $totalVolume += $set->weight * $set->reps;
                });
            });

            dd($totalVolume);
            return [
                'day'               => $finishDate->day,
                'finishedAt'        => $finishDate->diffForHumans(),
                'label'             => $mesoDay->label,
                'exercisesCount'    => $mesoDay->dayExercises->count(),
                'setsCount'         => $setsCount,
                'totalVolume'
            ];
        });

        return Inertia::render('Dashboard', [
            'bestLifts' => $mappedLifts,
            'activity'  => $activity,
            'lastWorkouts' => $lastWorkouts
        ]);
    }
}
