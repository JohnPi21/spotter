<?php

namespace App\Services;

use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MuscleGroup;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DashboardTraining
{
    public function forUser(int $userId): array
    {
        $now = now();
        $currentWeek = $now->copy()->startOfWeek(CarbonInterface::MONDAY);
        $start = $currentWeek->copy()->subWeeks(7);
        $active = Mesocycle::query()->ownedBy($userId)->active()->orderByDesc('id')->first();
        $planned = $active?->planned_stats;

        $counts = ExerciseSet::query()->ownedBy($userId)
            ->join('day_exercises', 'exercise_sets.day_exercise_id', '=', 'day_exercises.id')
            ->join('exercises', 'day_exercises.exercise_id', '=', 'exercises.id')
            ->whereBetween('exercise_sets.finished_at', [$start, $now])
            ->selectRaw('DATE_SUB(DATE(exercise_sets.finished_at), INTERVAL WEEKDAY(exercise_sets.finished_at) DAY) as week_start')
            ->selectRaw('exercises.muscle_group_id, COUNT(*) as sets_count')
            ->groupBy('week_start', 'exercises.muscle_group_id')
            ->get();

        $sessions = MesoDay::query()->ownedBy($userId)
            ->whereBetween('finished_at', [$start, $now])
            ->selectRaw('DATE_SUB(DATE(finished_at), INTERVAL WEEKDAY(finished_at) DAY) as week_start, COUNT(*) as sessions_count')
            ->groupBy('week_start')->get()->keyBy('week_start');

        $weeks = [];

        for ($index = 0; $index < 8; $index++) {
            $week = $start->copy()->addWeeks($index);
            $key = $week->toDateString();
            $weeks[] = [
                'start' => $key,
                'label' => $week->format('d M'),
                'range' => $week->format('d M').' – '.$week->copy()->addDays(6)->format('d M Y'),
                'isCurrent' => $index === 7,
                'sets' => (int) $counts->where('week_start', $key)->sum('sets_count'),
                'sessions' => (int) ($sessions->get($key)?->getAttribute('sessions_count') ?? 0),
            ];
        }

        $plannedGroups = collect($planned['muscle_groups'] ?? [])->keyBy('id');
        $ids = $counts->pluck('muscle_group_id')->merge($plannedGroups->keys())->unique();
        $groups = MuscleGroup::query()->whereIn('id', $ids)->orderBy('name')->get()->map(function (MuscleGroup $group) use ($counts, $weeks, $plannedGroups, $planned): array {
            $groupCounts = $counts->where('muscle_group_id', $group->id)->keyBy('week_start');

            return [
                'id' => $group->id,
                'name' => $group->name,
                'sets' => array_map(fn (array $week) => (int) ($groupCounts->get($week['start'])?->getAttribute('sets_count') ?? 0), $weeks),
                'plannedSets' => $planned !== null ? (int) ($plannedGroups->get($group->id)['sets'] ?? 0) : null,
            ];
        })->values()->all();

        return [
            'weeks' => $weeks,
            'muscleGroups' => $groups,
            'activePlan' => $active ? [
                'id' => $active->id,
                'name' => $active->name,
                'daysPerWeek' => $active->days_per_week,
                'setsPerWeek' => $planned['sets_per_week'] ?? null,
                'muscleGroupsCount' => $planned['muscle_groups_count'] ?? null,
            ] : null,
            'recentWorkouts' => $this->recentWorkouts($userId),
            'timezone' => config('app.timezone'),
        ];
    }

    private function recentWorkouts(int $userId): array
    {
        return MesoDay::query()->ownedBy($userId)->whereNotNull('finished_at')->where('finished_at', '<=', now())
            ->with([
                'mesocycle:id,name,unit',
                'dayExercises.exercise.muscleGroup',
                'dayExercises.sets' => fn ($query) => $query->whereNotNull('finished_at'),
            ])
            ->orderByDesc('finished_at')->orderByDesc('id')->limit(3)->get()
            ->map(function (MesoDay $day): array {
                $exercises = $day->dayExercises->filter(fn ($exercise) => $exercise->sets->isNotEmpty());
                $sets = $exercises->flatMap->sets;
                $groups = $exercises->groupBy('exercise.muscle_group_id')->map(function ($exercises): array {
                    return [
                        'name' => $exercises->first()->exercise->muscleGroup->name ?? 'Unknown muscle group',
                        'sets' => $exercises->sum(fn ($exercise) => $exercise->sets->count()),
                    ];
                })->values()->all();

                return [
                    'id' => $day->id,
                    'mesocycleId' => $day->mesocycle_id,
                    'mesocycleName' => $day->mesocycle->name,
                    'label' => $day->label,
                    'finishedAt' => Carbon::parse($day->finished_at)->diffForHumans(),
                    'sets' => $sets->count(),
                    'exercises' => $exercises->count(),
                    'muscleGroups' => $groups,
                    'load' => round((float) $sets->sum('weight'), 2),
                    'volume' => round((float) $sets->sum(fn ($set) => (float) $set->weight * (int) $set->reps), 2),
                    'unit' => $day->mesocycle->unit,
                ];
            })->all();
    }
}
