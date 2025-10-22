<?php

namespace App\Actions\MesoDay;

use App\Data\Mesocycle\DayTemplateData;
use Illuminate\Support\Facades\DB;

class CreateMesoDay
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * @template DayTemplateData
     * @param DayTemplateData[] $days
     * @param int $mesocycleId
     * @param int $weeksDuration
     * @return void
     */
    public function execute(array $days, int $mesocycleId, int $weeksDuration)
    {
        $mesoDays = [];

        for ($i = 1; $i <= $weeksDuration; $i++) {

            foreach ($days as $idx => $day) {
                $mesoDays[] = [
                    "mesocycle_id" => $mesocycleId,
                    "week"         => $i,
                    "day_order"    => $idx + 1,
                    "label"        => $day->label,
                    "position"     => $idx,
                ];
            }
        }

        DB::table('meso_days')->insert($mesoDays);
    }
}
