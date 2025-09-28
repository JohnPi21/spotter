<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;

class MakeCalendarAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Invoke the class instance.
     */
    public static function execute(Mesocycle $mesocycle): array
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
