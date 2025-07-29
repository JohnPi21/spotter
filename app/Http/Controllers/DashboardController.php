<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(): \Inertia\Response
    {
        $biggestByWeight = ExerciseSet::selectRaw('day_exercise_id, MAX(weight) as best_weight')
            ->whereHas('dayExercise.day.mesocycle', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->groupBy('day_exercise_id')
            ->orderByDesc('best_weight')
            ->limit(3)
            ->get();

        $biggestByVolume = ExerciseSet::selectRaw('day_exercise_id, MAX(reps * weight) as best_volume')
            ->whereHas('dayExercise.day.mesocycle', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->groupBy('day_exercise_id')
            ->orderByDesc('best_volume')
            ->limit(3)
            ->get();

        $bestLifts = ['biggestByWeight' => $biggestByWeight, 'biggestByVolume' => $biggestByVolume];

        $activity = [];

        $lastWorkouts = [];

        return Inertia::render('Dashboard', [
            'bestLifts' => $bestLifts,
            'activity'  => $activity,
            'lastWorkouts' => $lastWorkouts
        ]);
    }
}
