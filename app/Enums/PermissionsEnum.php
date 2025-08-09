<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case EDIT_PROFILE         = 'edit profile';

    public function label(): string
    {
        return match ($this) {
            static::EDIT_PROFILE        => 'edit profile',
        };
    }
}
