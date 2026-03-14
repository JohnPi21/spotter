<?php

namespace App\Enums;

enum RequestStatusEnum: string
{
    use EnumHelpers;

    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
