<?php

namespace App\Listeners;

use App\Enums\RolesEnum;
use Illuminate\Auth\Events\Registered;

class AssignDefaultUserRole
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
    public function handle(Registered $event): void
    {
        $event->user->assignRole(RolesEnum::USER);
    }
}
