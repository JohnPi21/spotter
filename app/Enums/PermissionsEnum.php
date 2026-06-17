<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case EDIT_PROFILE = 'edit profile';

    public function label(): string
    {
        return match ($this) {
            self::EDIT_PROFILE => 'edit profile',
        };
    }
}
