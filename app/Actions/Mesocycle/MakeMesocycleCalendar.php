<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;

class MakeMesocycleCalendar
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * Invoke the class instance.
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
