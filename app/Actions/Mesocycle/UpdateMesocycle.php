<?php

declare(strict_types=1);

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\MesocycleStructureTypes;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MesoTemplate;
use App\Services\MesocyclePlan;
use App\Services\MesocycleStructureHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-import-type CompletedMesoDay from MesocycleStructureTypes
 * @phpstan-import-type DayExerciseIdMapItem from MesocycleStructureTypes
 * @phpstan-import-type MesoDayIdMapItem from MesocycleStructureTypes
 */
class UpdateMesocycle
{
    public function __construct(protected MesocycleStructureHelper $mesoStructure, protected MesocyclePlan $plan) {}

    public function execute(CreateMesocycleData $mesoDTO, Mesocycle $mesocycle, bool $updateTemplate = false): void
    {
        DB::transaction(function () use ($mesoDTO, $mesocycle, $updateTemplate) {

            $mesocycle = Mesocycle::query()->lockForUpdate()->findOrFail($mesocycle->id);
            $payload = $this->plan->fromData($mesoDTO);
            $payload['unit'] ??= $mesocycle->unit;
            $stats = $this->plan->stats($payload);

            if ($updateTemplate) {
                $template = $mesocycle->mesoTemplate()->lockForUpdate()->first();

                if (! $template || $template->user_id !== $mesocycle->user_id) {
                    throw ValidationException::withMessages([
                        'updateTemplate' => 'You can only update a linked template that you own.',
                    ]);
                }

                $this->preserveLegacyPlans($template, $mesocycle);

                $template->update([
                    'schema' => $payload,
                    'planned_stats' => $stats,
                    'name' => $mesoDTO->name,
                    'frequency' => count($mesoDTO->days),
                ]);
            }

            $mesocycle->update([
                'name' => $mesoDTO->name,
                'unit' => $payload['unit'],
                'schema' => $payload,
                'planned_stats' => $stats,
                'weeks_duration' => $mesoDTO->weeksDuration,
                'days_per_week' => count($mesoDTO->days),
            ]);

            $completedDays = $this->getCompletedDays($mesocycle);

            $daysIdsMap = $this->updateMesoDays($mesocycle->id, $mesoDTO, $completedDays);

            $dayExerciseMap = $this->createDayExercises($mesoDTO, $daysIdsMap);

            $this->mesoStructure->createSets($dayExerciseMap, $mesoDTO);
        }, attempts: 3);
    }

    private function preserveLegacyPlans(MesoTemplate $template, Mesocycle $editedMesocycle): void
    {
        foreach ($template->mesocycles()->whereKeyNot($editedMesocycle->id)->whereNull('schema')->lockForUpdate()->lazyById(100) as $mesocycle) {
            try {
                $resolved = $this->plan->resolve($mesocycle);
                $mesocycle->schema = $resolved['schema'];
                $mesocycle->planned_stats = $this->plan->stats($resolved['schema']);
                $mesocycle->timestamps = false;
                $mesocycle->saveQuietly();
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'updateTemplate' => 'Another mesocycle still depends on this template and its plan could not be preserved.',
                ]);
            }
        }
    }

    /**
     * @phpstan-return list<CompletedMesoDay>
     */
    private function getCompletedDays(Mesocycle $mesocycle): array
    {
        $days = $mesocycle->days()->whereNotNull('finished_at')->get();

        return $days->map(function ($day) {
            return [
                'id' => $day->id,
                'week' => $day->week,
                'dayOrder' => $day->day_order,
            ];
        })->toArray();
    }

    /**
     * @phpstan-param  list<CompletedMesoDay>  $completedDays
     *
     * @phpstan-return list<MesoDayIdMapItem>
     */
    private function updateMesoDays(int $mesocycleId, CreateMesocycleData $mesoDTO, array $completedDays): array
    {
        $mesoDays = $this->mesoStructure->buildMesoDays($mesoDTO, $completedDays);

        $completedDaysIds = $this->getCompletedDaysIds($completedDays);

        // Delete the days first
        $this->deleteMesoDays($mesocycleId, $completedDaysIds);

        // Insert the new ones
        $this->mesoStructure->insertMesoDays($mesocycleId, $mesoDays);

        // Get the inserted days
        $ids = $this->getInsertedDays($mesocycleId, $completedDaysIds);

        return $this->mesoStructure->buildDaysIdsMap($mesoDays, $ids);
    }

    /**
     * @param  list<int>  $completedDaysIds
     * @return list<int>
     */
    private function getInsertedDays(int $mesocycleId, array $completedDaysIds): array
    {
        return MesoDay::where('mesocycle_id', $mesocycleId)
            ->orderBy('week')
            ->orderBy('day_order')
            ->whereNotIn('id', $completedDaysIds)
            ->pluck('id')->toArray();
    }

    /**
     * @param  list<int>  $completedDaysIds
     */
    private function deleteMesoDays(int $mesocycleId, array $completedDaysIds): void
    {
        MesoDay::where('mesocycle_id', $mesocycleId)->whereNotIn('id', $completedDaysIds)->delete();
    }

    /**
     * @phpstan-param  list<CompletedMesoDay>  $completedDays
     *
     * @return list<int>
     */
    private function getCompletedDaysIds(array $completedDays): array
    {
        return array_column($completedDays, 'id');
    }

    /**
     * @phpstan-param  list<MesoDayIdMapItem>  $mesoDaysMap
     *
     * @phpstan-return list<DayExerciseIdMapItem>
     */
    private function createDayExercises(CreateMesocycleData $mesoDTO, array $mesoDaysMap): array
    {
        [$dayExercises, $dayExercisesMap] = $this->mesoStructure->buildDayExercises($mesoDTO, $mesoDaysMap);

        $ids = $this->mesoStructure->insertDayExercises($dayExercises);

        return $this->mesoStructure->buildDayExercisesIdsMap($ids, $dayExercisesMap);
    }
}
