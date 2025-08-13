<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AppException extends HttpException
{
    public ?string $error;

    public function __construct(
        int $status = 400,
        string $message = 'Something went wrong',
        ?string $error,
        ?\Throwable $previous = null,
        array $headers = [],
    ) {
        parent::__construct($status, $message, $previous, $headers);
        $this->error = $error;
    }

    public static function forbidden(string $msg = 'Action not allowed'): self
    {
        return new self(403, $msg, error: 'FORBIDDEN');
    }

    public static function unprocessable(string $msg = 'Invalid data.'): self
    {
        return new self(422, $msg, error: 'UNPROCESSABLE');
    }

    public static function tooManyRequests(string $msg = 'Too many requests.', int $retryAfter = 60): self
    {
        return new self(429, $msg, error: 'RATE_LIMIT', headers: ['Retry-After' => $retryAfter]);
    }
}
