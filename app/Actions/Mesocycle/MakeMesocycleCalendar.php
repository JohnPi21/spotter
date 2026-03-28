<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;
use App\Models\MesoDay;

class MakeMesocycleCalendar
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * @return array<int, list<MesoDay>>
     */
    public function execute(Mesocycle $mesocycle): array
    {
        $calendar = [];
        $weekIdx = 1;

        foreach ($mesocycle->days as $d) {
            $calendar[$weekIdx][] = $d;

            if (count($calendar[$weekIdx]) == $mesocycle->days_per_week) {
                $weekIdx++;
            }
        }

        return $calendar;
    }
}
