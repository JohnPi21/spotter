<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\MesocycleStructureTypes;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MesoTemplate;
use App\Services\MesocyclePlan;
use App\Services\MesocycleStructureHelper;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-import-type DayExerciseIdMapItem from MesocycleStructureTypes
 * @phpstan-import-type MesoDayIdMapItem from MesocycleStructureTypes
 */
class CreateMesocycle
{
    public function __construct(protected MesocycleStructureHelper $mesoStructure, protected MesocyclePlan $plan) {}

    public function execute(CreateMesocycleData $mesoDTO, int $userId, bool $userHasActiveMeso): void
    {
        DB::transaction(function () use ($userId, $mesoDTO, $userHasActiveMeso) {

            $daysPerWeek = count($mesoDTO->days);

            $payload = $this->plan->fromData($mesoDTO);
            $stats = $this->plan->stats($payload);

            $mesoTemplate = MesoTemplate::create([
                'schema' => $payload,
                'planned_stats' => $stats,
                'name' => $mesoDTO->name,
                'frequency' => $daysPerWeek,
                'user_id' => $userId,
            ]);

            $mesocycle = Mesocycle::create([
                'name' => $mesoDTO->name,
                'unit' => $mesoDTO->unit,
                'weeks_duration' => $mesoDTO->weeksDuration,
                'days_per_week' => $daysPerWeek,
                'user_id' => $userId,
                'meso_template_id' => $mesoTemplate->id,
                'schema' => $payload,
                'planned_stats' => $stats,
                'status' => ! $userHasActiveMeso,
            ]);

            $daysIdsMap = $this->createMesoDays($mesocycle->id, $mesoDTO);

            $dayExerciseMap = $this->createDayExercises($mesoDTO, $daysIdsMap);

            $this->mesoStructure->createSets($dayExerciseMap, $mesoDTO);
        });
    }

    /**
     * @return list<MesoDayIdMapItem>
     */
    private function createMesoDays(int $mesocycleId, CreateMesocycleData $mesoDTO): array
    {
        $mesoDays = $this->mesoStructure->buildMesoDays($mesoDTO);

        $this->mesoStructure->insertMesoDays($mesocycleId, $mesoDays);

        $ids = $this->getInsertedDays($mesocycleId);

        return $this->mesoStructure->buildDaysIdsMap($mesoDays, $ids);
    }

    /**
     * @return list<int>
     */
    private function getInsertedDays(int $mesocycleId): array
    {
        return MesoDay::where('mesocycle_id', $mesocycleId)
            ->orderBy('week')
            ->orderBy('day_order')
            ->pluck('id')->all();
    }

    /**
     * @param  list<MesoDayIdMapItem>  $mesoDaysMap
     * @return list<DayExerciseIdMapItem>
     */
    private function createDayExercises(CreateMesocycleData $mesoDTO, array $mesoDaysMap): array
    {
        [$dayExercises, $dayExercisesMap] = $this->mesoStructure->buildDayExercises($mesoDTO, $mesoDaysMap);

        $ids = $this->mesoStructure->insertDayExercises($dayExercises);

        return $this->mesoStructure->buildDayExercisesIdsMap($ids, $dayExercisesMap);
    }
}
