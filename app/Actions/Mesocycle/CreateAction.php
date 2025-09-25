<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateData;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Support\Facades\Auth;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Illuminate\Support\Facades\DB;

class CreateAction
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public static function execute(CreateData $mesoDTO): void
    {
        $activateMeso = ! Mesocycle::mine()->exists();

        DB::transaction(function () use ($mesoDTO, $activateMeso) {
            $mesocycle = Mesocycle::create([
                'name'            => $mesoDTO->name,
                'unit'            => $mesoDTO->unit,
                'weeks_duration'  => $mesoDTO->weeks_duration,
                'days_per_week'   => count($mesoDTO->days),
                'user_id'         => Auth::id(),
                'status'          => !!$activateMeso
            ]);


            $mesoDays = [];

            for ($i = 1; $i <= $mesocycle->weeks_duration; $i++) {

                foreach ($mesoDTO->days as $idx => $day) {
                    $mesoDays[] = [
                        "mesocycle_id" => $mesocycle->id,
                        "week"         => $i,
                        "day_order"    => $idx + 1,
                        "label"        => $day->label,
                        "position"     => $idx,
                    ];
                }
            }

            DB::table('meso_day')->insert($mesoDays);

            $daysIds = MesoDay::where('mesocycle_id', $mesocycle->id)
                ->orderBy('id')
                ->pluck('id');

            $exercises = [];

            foreach ($daysIds as $dayIdx => $dayId) {
                foreach ($mesoDTO->days[$dayIdx]->exercises as $pos => $exercise) {
                    $exercises[] = [
                        "meso_day_id" => $dayId,
                        "exercise_id" => $exercise->exerciseID,
                        "position"    => $pos,
                    ];
                }
            }

            // Finish batch inserting exercises 

            // foreach ($day->exercises as $position => $exercise) {
            //     $exerciseDay = DayExercise::create([
            //         "meso_day_id" => $createdDay->id,
            //         "exercise_id" => $exercise->exerciseID,
            //         "position"    => (int)$position,
            //     ]);

            //     ExerciseSet::create([
            //         "day_exercise_id"   => $exerciseDay->id,
            //     ]);
            // }
        });
    }
}
