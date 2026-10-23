<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Subscribed listeners:
 *
 * @see \App\Listeners\ToggleMesocycle Updates mesocycle completion synchronously.
 * @see \App\Listeners\UpdateProgressTargets Queues progression updates for finished days after commit.
 */
class DayToggled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  int  $dayId  ID of MesoDay
     */
    public function __construct(
        public int $dayId
    ) {}
}
