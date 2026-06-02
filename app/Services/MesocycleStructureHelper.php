<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\MesocycleStructureTypes;
use App\Data\Mesocycle\SetTemplateData;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\MesoDay;

// Remove any inserting logic from this into the parent (no matter a little duplication or I can put the insert into another class or on models)
// Remove sets builder from here and in to each action, just keep the building of the sets here

/**
 * @phpstan-import-type BuiltMesoDay from MesocycleStructureTypes
 * @phpstan-import-type CompletedMesoDay from MesocycleStructureTypes
 * @phpstan-import-type DayExerciseBuild from MesocycleStructureTypes
 * @phpstan-import-type DayExerciseIdMapItem from MesocycleStructureTypes
 * @phpstan-import-type DayExerciseMapItem from MesocycleStructureTypes
 * @phpstan-import-type MesoDayIdMapItem from MesocycleStructureTypes
 */
class MesocycleStructureHelper
{
    /**
     * @phpstan-param  list<CompletedMesoDay>  $completedDays
     *
     * @phpstan-return list<BuiltMesoDay>
     */
    public function buildMesoDays(CreateMesocycleData $mesoDTO, array $completedDays = []): array
    {
        $mesoDays = [];

        for ($week = 1; $week <= $mesoDTO->weeksDuration; $week++) {
            foreach ($mesoDTO->days as $idx => $day) {
                $order = (int) $idx + 1;

                if ($this->skipDay($completedDays, $week, $order)) {
                    continue;
                }

                $mesoDays[] = [
                    'week' => $week,
                    'day_order' => $order,
                    'label' => $day->label,
                    'position' => $idx,
                ];
            }
        }

        return $mesoDays;
    }

    /**
     * @phpstan-param  list<CompletedMesoDay>  $completedDays
     */
    private function skipDay(array $completedDays, int $week, int $order): bool
    {
        if (count($completedDays) == 0) {
            return false;
        }

        foreach ($completedDays as $dayToSkip) {
            if ($dayToSkip['week'] == $week && $dayToSkip['dayOrder'] == $order) {
                return true;
            }
        }

        return false;
    }

    /**
     * @phpstan-param  list<BuiltMesoDay>  $mesoDays
     */
    public function insertMesoDays(int $mesocycleId, array $mesoDays): void
    {
        $mesoDays = array_map(function ($day) use ($mesocycleId) {
            $day['mesocycle_id'] = $mesocycleId;

            return $day;
        }, $mesoDays);

        MesoDay::insert($mesoDays);
    }

    /**
     * @param  list<int>  $ids
     *
     * @phpstan-param  list<BuiltMesoDay>  $mesoDays
     *
     * @phpstan-return list<MesoDayIdMapItem>
     */
    public function buildDaysIdsMap(array $mesoDays, array $ids): array
    {
        $daysMap = [];

        foreach ($mesoDays as $idx => $mesoDay) {
            $daysMap[] = [
                'week' => $mesoDay['week'],
                'dayOrder' => $mesoDay['day_order'],
                'mesoDayId' => $ids[$idx],
            ];
        }

        return $daysMap;
    }

    /**
     * @phpstan-param  list<MesoDayIdMapItem>  $mesoDaysMap
     *
     * @phpstan-return array{list<DayExerciseBuild>, list<DayExerciseMapItem>}
     */
    public function buildDayExercises(CreateMesocycleData $mesoDTO, array $mesoDaysMap): array
    {
        $dayExercises = [];

        $dayExercisesMap = [];

        foreach ($mesoDaysMap as $mesoDay) {
            $orderToIdx = $mesoDay['dayOrder'] - 1;

            foreach ($mesoDTO->days[$orderToIdx]->exercises as $pos => $exercise) {
                $dayExercises[] = [
                    'meso_day_id' => $mesoDay['mesoDayId'],
                    'exercise_id' => $exercise->exerciseId,
                    'one_rep_max' => $exercise->oneRepMax ?? null,
                    'position' => $pos + 1,
                ];

                $dayExercisesMap[] = [
                    'dayOrder' => $orderToIdx,
                    'position' => $pos,
                ];
            }
        }

        return [$dayExercises, $dayExercisesMap];
    }

    /**
     * @phpstan-param  list<DayExerciseBuild>  $dayExercises
     *
     * @return list<int>
     */
    public function insertDayExercises(array $dayExercises): array
    {
        DayExercise::insert($dayExercises);

        $ids = array_unique(array_column($dayExercises, 'meso_day_id'));

        return DayExercise::whereIn('meso_day_id', $ids)
            ->orderBy('id')
            ->pluck('id')->all();
    }

    /**
     * @param  list<int>  $ids
     *
     * @phpstan-param  list<DayExerciseMapItem>  $dayExercisesMap
     *
     * @phpstan-return list<DayExerciseIdMapItem>
     */
    public function buildDayExercisesIdsMap(array $ids, array $dayExercisesMap): array
    {
        $dayExercisesIdsMap = [];

        foreach ($dayExercisesMap as $idx => $dayExercise) {
            $dayExercisesIdsMap[] = [
                ...$dayExercise,
                'dayExerciseId' => $ids[$idx],
            ];
        }

        return $dayExercisesIdsMap;
    }

    /**
     * @phpstan-param  list<DayExerciseIdMapItem>  $dayExercisesMap
     */
    public function createSets(array $dayExercisesMap, CreateMesocycleData $mesoDTO): void
    {
        $sets = [];
        $emptyLoad = SetTemplateData::emptyPayload();

        foreach ($dayExercisesMap as $mi => $map) {

            $definedSets = $mesoDTO->days[$map['dayOrder']]->exercises[$map['position']]->sets;

            // Sets may have null values, we filter them here and check again
            $definedSets = collect($definedSets)->filter(fn (SetTemplateData $item) => collect($item)->contains(fn ($value) => $value != null));

            if ($definedSets->isEmpty()) {
                $sets[] = [
                    ...$emptyLoad,
                    'day_exercise_id' => $map['dayExerciseId'],
                ];
                $dayExercisesMap[$mi]['setsCount'] = 1;

                continue;
            }

            foreach ($definedSets as $set) {
                $sets[] = [
                    ...$emptyLoad,
                    ...$set->toArray(),
                    'day_exercise_id' => $map['dayExerciseId'],
                ];
            }

            $dayExercisesMap[$mi]['setsCount'] = count($definedSets);
        }

        ExerciseSet::insert($sets);
    }
}
