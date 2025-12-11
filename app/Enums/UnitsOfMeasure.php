<?php

namespace App\Enums;

enum UnitsOfMeasure: string
{
    use EnumHelpers;

    case KG = 'kg';
    case LB = 'lb';
}
