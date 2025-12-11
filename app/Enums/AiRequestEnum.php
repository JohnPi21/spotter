<?php

namespace App\Enums;

enum AiRequestEnum: string
{
    use EnumHelpers;

    case TEXT       = 'asText';
    case STRUCTURED = 'asStructured';
    case CHAT       = 'asChat';
}
