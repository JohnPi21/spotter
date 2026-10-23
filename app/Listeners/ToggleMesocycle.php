<?php

namespace App\Listeners;

use App\Events\DayToggled;
use App\Models\MesoDay;

class ToggleMesocycle
{
    /**
     * Handle the event.
     */
    public function handle(DayToggled $event): void
    {
        $mesocycle = MesoDay::query()->findOrFail($event->dayId)->mesocycle;
        $hasUnfinishedDays = $mesocycle->days()->whereNull('finished_at')->exists();

        if ($hasUnfinishedDays && $mesocycle->finished_at !== null) {
            $mesocycle->update(['finished_at' => null]);
        } elseif (! $hasUnfinishedDays && $mesocycle->finished_at === null) {
            $mesocycle->update(['finished_at' => now()]);
        }
    }
}
