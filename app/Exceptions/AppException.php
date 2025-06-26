<?php

namespace App\Exceptions;

use Exception;

class AppException extends Exception
{
    public function __construct(string $message = "Something went wrong", int $status = 400)
    {
        parent::__construct($message, $status);
    }
}
