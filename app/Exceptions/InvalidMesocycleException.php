<?php

namespace App\Exceptions;

class InvalidMesocycleException extends DomainException
{
    public function errorCode(): string
    {
        return 'AI_INVALID_MESOCYCLE';
    }
}
