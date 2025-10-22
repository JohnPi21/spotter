<?php

namespace App\Actions\Mesocycle;

use App\Actions\DayExercise\CreateManyDayExercises;
use App\Actions\MesoDay\CreateMesoDay;
use App\Data\Mesocycle\CreateMesocycleData;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\DayExercise;
use Illuminate\Support\Facades\DB;

class CreateMesocycle
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CreateMesoDay $createMesoDays, private CreateManyDayExercises $createManyDayExercises) {}

    public function execute(CreateMesocycleData $mesoDTO, int $userId): void
    {
        DB::transaction(function () use ($mesoDTO, $userId) {
            $mesocycle = Mesocycle::create([
                'name'            => $mesoDTO->name,
                'unit'            => $mesoDTO->unit,
                'weeks_duration'  => $mesoDTO->weeks_duration,
                'days_per_week'   => count($mesoDTO->days),
                'user_id'         => $userId,
                'status'          => !!Mesocycle::userHasActiveMeso($userId),
            ]);

            $this->createMesoDays->execute($mesoDTO->days, $mesocycle->id, $mesocycle->weeks_duration);

            $daysIds = MesoDay::where('mesocycle_id', $mesocycle->id)
                ->orderBy('id')
                ->pluck('id')->all();

            $this->createManyDayExercises->execute($mesoDTO->days, $daysIds);

            $dayExercisesIds = DayExercise::whereIn('meso_day_id', $daysIds)
                ->orderBy('id')
                ->pluck('id');

            $sets = array_map(function ($dayExId) {
                return ['day_exercise_id' => $dayExId];
            }, $dayExercisesIds->toArray());

            DB::table('exercise_sets')->insert($sets);
        });
    }
}
