<?php

namespace App\Enums;

enum ExperienceEnum: string
{
    use EnumHelpers;

    case BEGINNER       = 'beginner';
    case INTERMEDIATE   = 'intermediate';
    case ADVANCED       = 'advanced';
}
