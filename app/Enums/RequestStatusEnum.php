<?php

namespace App\Enums;

use App\Enums\EnumHelpers;

enum RequestStatusEnum: string
{
    use EnumHelpers;

    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED  = 'failed';
}
