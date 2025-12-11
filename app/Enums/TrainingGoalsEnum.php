<?php

namespace App\Enums;

enum TrainingGoalsEnum: string
{
    use EnumHelpers;

    case HYPERTROPHY = 'hypertrophy';
    case STRENGTH    = 'strength';
    case FAT_LOSS    = 'fat_loss';
    case RECOMP      = 'recomp';
}
