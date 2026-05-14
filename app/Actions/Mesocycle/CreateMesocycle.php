<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\SetTemplateData;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MesoTemplate;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

// @TODO: check for bugs
// Make the creation of dayExercises and sets reusable by receiving daysIds from outside and DayTemplate
// Extract APplyDayTemplateDays => buildDayExerciseRows buildDayExerciseLookup BuildSetRows
// Also, try to make a lookup map for week:day:dayExercise => set so i can easily find what i need without relying too much on positional bs
class CreateMesocycle
{
    public function execute(CreateMesocycleData $mesoDTO, int $userId, bool $status): void
    {
        DB::transaction(function () use ($userId, $mesoDTO, $status) {

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
                'status' => $status,
            ]);

            $daysIds = $this->createMesoDays($mesocycle->id, $mesoDTO);

            $dayExerciseMap = $this->createDayExercises($mesoDTO, $daysIds, $daysPerWeek);

            $this->createSets($dayExerciseMap, $mesoDTO);
        });
    }

    /**
     * @param  CreateMesocycleData  $mesoDTO
     * @return array<int, int>
     */
    private function createMesoDays(int $mesocycleId, $mesoDTO): array
    {
        $mesoDays = [];

        for ($i = 1; $i <= $mesoDTO->weeksDuration; $i++) {
            foreach ($mesoDTO->days as $idx => $day) {
                $mesoDays[] = [
                    'mesocycle_id' => $mesocycleId,
                    'week' => $i,
                    'day_order' => (int) $idx + 1,
                    'label' => $day->label,
                    'position' => $idx,
                ];
            }
        }
        MesoDay::insert($mesoDays);

        return MesoDay::where('mesocycle_id', $mesocycleId)
            ->orderBy('id')
            ->pluck('id')->all();
    }

    /**
     * @param  array<int, int>  $daysIds
     * @return array<int, array{position: int, dayOrder: int}>
     */
    private function createDayExercises(CreateMesocycleData $mesoDTO, array $daysIds, int $daysPerWeek): array
    {
        $dayExercises = [];

        $dayExercisesMap = [];

        // We go over each inserted dayId (which would be nr of days * daysPerWeek)
        // Let's say the user created 4 days with 6 weeks => we have in total 24 days
        // So the first day out of those 24 maps to the first day of $daysDTO (dayId 1 => daysDTO 0)
        // 2 => 1 | 3 => 2 | 4 => 3 | 5 => 0 again because of %
        // So we insert the days in this sequence
        // day 1 all day exercises, day 2 all day exercises...
        // OR dayExercise with day id 1, day exercise with day id 1, day exercise with day id 1 until we add all exercises from index 0 from daysDTO
        // OR week 1 day 1, week 1 day 2, week 1 day 4
        foreach ($daysIds as $dayIdx => $dayId) {
            $orderInWeek = $dayIdx % $daysPerWeek;

            foreach ($mesoDTO->days[$orderInWeek]->exercises as $pos => $exercise) {
                $dayExercises[] = [
                    'meso_day_id' => $dayId,
                    'exercise_id' => $exercise->exerciseId,
                    'one_rep_max' => $exercise->oneRepMax ?? null,
                    'position' => $pos + 1,
                ];

                $dayExercisesMap[] = ['position' => $pos, 'dayOrder' => $orderInWeek];
            }
        }
        DayExercise::insert($dayExercises);

        $dayExercisesIds = DayExercise::whereIn('meso_day_id', $daysIds)
            ->orderBy('id')
            ->pluck('id')->all();

        foreach ($dayExercisesIds as $idx => $dayExerciseId) {
            $dayExercisesMap[$idx]['dayExerciseId'] = $dayExerciseId;
        }

        return $dayExercisesMap;
    }

    /**
     * @param  CreateMesocycleData  $mesoDTO
     */
    private function createSets(array $dayExercisesMap, $mesoDTO): void
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
