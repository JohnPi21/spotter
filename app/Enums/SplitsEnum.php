<?php

namespace App\Enums;

enum SplitsEnum: string
{
    use EnumHelpers;

    case FULL_BODY      = 'full_body';
    case UPPER_LOWER    = 'upper_lower';
    case PUSH_PULL_LEGS = 'push_pull_legs';
    case BRO_SPLIT      = 'bro_split';
    case CUSTOM         = 'custom';
}
