<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\MesocycleStructureTypes;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MesoTemplate;
use App\Services\MesocycleStructureHelper;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

/**
 * @phpstan-import-type DayExerciseIdMapItem from MesocycleStructureTypes
 * @phpstan-import-type MesoDayIdMapItem from MesocycleStructureTypes
 */
class CreateMesocycle
{
    public function __construct(protected MesocycleStructureHelper $mesoStructure) {}

    public function execute(CreateMesocycleData $mesoDTO, int $userId, bool $userHasActiveMeso): void
    {
        DB::transaction(function () use ($userId, $mesoDTO, $userHasActiveMeso) {

            $daysPerWeek = count($mesoDTO->days);

            // Get data as array with camelCase keys
            $payload = $mesoDTO->transform(
                TransformationContextFactory::create()->withoutPropertyNameMapping()
            );

            $mesoTemplate = MesoTemplate::create([
                'schema' => json_encode($payload),
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
