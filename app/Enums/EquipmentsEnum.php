<?php

namespace App\Enums;

enum EquipmentsEnum: string
{
    use EnumHelpers;

    case BARBELL              = 'barbell';
    case BODYWEIGHT_ONLY      = 'bodyweight-only';
    case MACHINE              = 'machine';
    case BODYWEIGHT_LOADABLE  = 'bodyweight_loadable';
    case CABLE                = 'cable';
    case DUMBBELL             = 'dumbbell';
    case SMITH_MACHINE        = 'smith-machine';
    case MACHINE_ASSISTANCE   = 'machine-assistance';
    case FREEMOTION           = 'freemotion';
}
