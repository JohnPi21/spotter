<?php

namespace App\Actions\DayExercise;

use Illuminate\Support\Facades\DB;
use App\Data\Mesocycle\DayTemplateData;

class CreateManyDayExercises
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * @template DayTemplateData
     * @param DayTemplateData[] $daysDTO
     * @return void
     */
    public function execute(array $daysDTO, array $existingDaysIds)
    {
        $dayExercises = [];

        $daysPerWeek = count($daysDTO);

        foreach ($existingDaysIds as $dayIdx => $dayId) {
            foreach ($daysDTO[$dayIdx % $daysPerWeek]->exercises as $pos => $exercise) {
                $dayExercises[] = [
                    "meso_day_id" => $dayId,
                    "exercise_id" => $exercise->exerciseID,
                    "position"    => $pos + 1,
                ];
            }
        }

        DB::table('day_exercises')->insert($dayExercises);
    }
}
