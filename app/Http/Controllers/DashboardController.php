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

        $selectorBy = $displayBy === 'weight' ? 'weight' : 'reps * weight';

        $bestLifts = ExerciseSet::query()
            ->join('day_exercises', 'exercise_sets.day_exercise_id', '=', 'day_exercises.id')
            ->join('exercises', 'day_exercises.exercise_id', '=', 'exercises.id')
            ->whereIn('day_exercises.exercise_id', [197, 26, 1]) // Squat / Benchpress / Deadlift
            ->whereHas(
                'dayExercise.day.mesocycle',
                fn($q) =>
                $q->where('user_id', Auth::id())
            )
            ->selectRaw("exercises.name as exercise_name, day_exercises.exercise_id, MAX($selectorBy) as best_value")
            ->groupBy('day_exercises.exercise_id', 'exercises.name')
            ->get();

        $mappedLifts = $bestLifts->map(function ($row) use ($displayBy) {
            $name = strtolower($row->exercise_name);
            $type = match (true) {
                str_contains($name, 'deadlift') => 'deadlift',
                str_contains($name, 'bench')    => 'bench',
                str_contains($name, 'squat')    => 'squat',
                default                         => 'other'
            };

            return [
                'exercise' => $row->exercise_name,
                'value'    => $row->best_value,
                'unit'     => $displayBy === 'weight' ? 'kg' : 'kg x reps',
                'type'     => $type
            ];
        });

        $lastMesoDays = MesoDay::with(['mesocycle', 'dayExercises.exercise.muscleGroup', 'dayExercises.sets'])
            ->ownedBy(Auth::id())
            ->orderByDesc('finished_at')
            ->limit(3)
            ->get();


        $graphActivity = MesoDay::select('id', 'label', 'week', 'updated_at', 'finished_at')
            ->with(['dayExercises.sets'])
            ->ownedBy(Auth::id())
            ->orderBy('week')
            ->orderBy('finished_at')
            ->limit(14)
            ->get();

        $graphMap = $graphActivity->map(function ($day) use ($displayBy) {
            $value = $day->dayExercises->flatMap->sets->sum(function ($set) use ($displayBy) {
                return $displayBy === 'weight'
                    ? $set->weight
                    : $set->weight * $set->reps;
            });

            return [
                'volumeY' => $value,
                'dateX'   => "{$day->label} " . $day?->finished_at?->isoFormat('DD.MM'),
            ];
        });

        $graph['data'] = $graphMap->pluck('volumeY');
        $graph['labels'] = $graphMap->pluck('dateX');


        $lastWorkouts = $lastMesoDays->map(function ($mesoDay) use ($displayBy) {
            $finishDate = Carbon::parse($mesoDay->finished_at);
            $setsCount = $mesoDay->dayExercises->sum(fn($dayExercise) => $dayExercise->sets->count());

            $muscleGroups = $mesoDay->dayExercises->pluck('exercise.muscleGroup.name')->unique()->values();

            $totalValue = $mesoDay->dayExercises->flatMap->sets->sum(function ($set) use ($displayBy) {
                return $displayBy === 'weight'
                    ? $set->weight
                    : $set->weight * $set->reps;
            });

            return [
                'day'            => $finishDate->day,
                'dayID'          => $mesoDay->id,
                'finishedAt'     => $finishDate->diffForHumans(),
                'label'          => $mesoDay->label,
                'exercisesCount' => $mesoDay->dayExercises->count(),
                'setsCount'      => $setsCount,
                'totalValue'     => $totalValue,
                'unit'           => $displayBy === 'weight' ? 'kg' : 'kg x reps',
                'muscleGroups'   => $muscleGroups,
                'mesocycle'      => $mesoDay->mesocycle_id
            ];
        });

        $info = [
            'memberFor' => Auth::user()->created_at->diffForHumans(),
        ];

        return Inertia::render('Dashboard', [
            'displayBy'     => $displayBy,
            'info'          => $info,
            'bestLifts'     => $mappedLifts,
            'activity'      => $graph,
            'lastWorkouts'  => $lastWorkouts,
        ]);
    }
}
