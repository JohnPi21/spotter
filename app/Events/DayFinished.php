<?php

namespace App\Events;

use App\Models\MesoDay;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DayFinished
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
