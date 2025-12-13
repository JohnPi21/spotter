<?php

namespace App\Exceptions;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    public function httpStatus(): int
    {
        return 422;
    }
    public function errorCode(): string
    {
        return 'DOMAIN_ERROR';
    }
}
