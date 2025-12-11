<?php

namespace App\Enums;

enum SessionDurationEnum: int
{
    use EnumHelpers;

    case MINUTES_30 = 30;
    case MINUTES_45 = 45;
    case MINUTES_60 = 60;
    case MINUTES_75 = 75;
    case MINUTES_90 = 90;
    case MINUTES_120 = 120;
}
