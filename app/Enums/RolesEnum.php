<?php

namespace App\Enums;

enum RolesEnum: string
{
    // case NAMEINAPP = 'name-in-database';

    case ADMIN  = 'admin';
    case GUEST  = 'guest';
    case USER   = 'user';
    case CLIENT = 'client';
    case COACH  = 'coach';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::ADMIN  => 'Admin',
            static::GUEST  => 'Guest',
            static::USER   => 'User',
            static::COACH  => 'Coach',
            static::CLIENT => 'Client',
        };
    }
}
