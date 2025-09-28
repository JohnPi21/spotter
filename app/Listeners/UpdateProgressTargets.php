<?php

namespace App\Listeners;

use App\Events\ExerciseSetSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProgressTargets
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExerciseSetSaved $event): void
    {
        //
    }
}
