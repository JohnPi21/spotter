<?php

namespace App\Enums;

enum RolesEnum: string
{
    // case NAMEINAPP = 'name-in-database';

    case ADMIN = 'admin';
    case GUEST = 'guest';
    case USER = 'user';
    case CLIENT = 'client';
    case COACH = 'coach';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::GUEST => 'Guest',
            self::USER => 'User',
            self::COACH => 'Coach',
            self::CLIENT => 'Client',
        };
    }
}
