<?php

namespace App\Services\RateLimiting;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class RateLimitRetrier
{
    /**
     * @var array<int, int>
     */
    private array $retryCodes = [408, 429, 500, 502, 503, 504];

    private int $maxSecondsToWait = 96;

    // TODO: AT some point in time I should refactor this
    // First: this class should just be a retriar, it should not have concepts of different possible exceptions
    // - I should create a RetryResolver that checks the exception types, is extensible and can be injected / extended from ouside if and when I want an
    // exception to be rehandled.
    // This class should only have the following steps
    // - execute callback
    // - catch throwable
    // - should it retry (resolve here with another class)
    // - after how much time should I retry (this also should come from outside)
    // What should be done:
    // Remove basically everything from the catch and just have a class injected that returns if the method should be retried and after how long
    // Move that logic to another side

    /**
     * Retry a callback with backoff
     *
     * @param  int  $maxSecondsToWait
     */
    public function run(callable $callback, int $maxAttempts, ?callable $when, ?callable $retryAfterCallback): mixed
    {
        // 1 < $maxAttempts < 10;
        // 7 attempts => 128 seconds at most wait time
        $maxAttempts = min(max($maxAttempts, 1), 7);
        $maxMicroToWait = $this->maxSecondsToWait * 1_000_000;
        $lastException = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                return $callback($attempt);
            } catch (Throwable $e) {
                $lastException = $e;
                Log::warning('Attempt: ', [$attempt, $e]);

                // Retry Connection exception because it never got to the server
                if ($e instanceof ConnectionException) {
                    $timeToWait = $this->exponentialRetry($attempt);

                    if ($timeToWait < $maxMicroToWait) {
                        usleep($timeToWait);

                        continue;
                    }
                }

                // Should retry
                if ($when) {
                    $shouldRetry = call_user_func($when, $e);

                    if (! $shouldRetry) {
                        throw $e;
                    }
                }

                if ($retryAfterCallback) {
                    // Get the retry timer
                    $retryAfter = call_user_func($retryAfterCallback, $e);

                    if ($retryAfter && $retryAfter < $this->maxSecondsToWait) {
                        sleep($retryAfter);

                        continue;
                    }
                }

                // Request exception has a response object
                if ($e instanceof RequestException) {
                    $response = $e->response;
                    $code = $response->status();

                    $retryAfterMicro = null;

                    if (! in_array($code, $this->retryCodes, true)) {
                        throw $e;
                    }

                    $retryAfterMicro = $this->returnRetryAfterInMicro($response->header('Retry-After'));

                    if ($retryAfterMicro !== null) {
                        if ($retryAfterMicro < $maxMicroToWait) {
                            usleep($retryAfterMicro);

                            continue;
                        }

                        throw $e;
                    }

                    $timeToWait = $this->exponentialRetry($attempt);

                    if ($timeToWait < $maxMicroToWait) {
                        usleep($timeToWait);

                        continue;
                    }

                    throw $e;
                }
            }
        }

        throw $lastException;
    }

    protected function exponentialRetry(int $attempt): int
    {
        $jitter = random_int(50_000, 250_000);

        return ((2 ** $attempt) * 1_000_000) + $jitter;
    }

    /**
     * Get Retry-After header value in microseconds if available
     */
    public function returnRetryAfterInMicro(int|string $retryAfter): ?int
    {
        if (is_numeric($retryAfter) && $retryAfter > 0) {
            return (int) $retryAfter * 1_000_000;
        }

        try {
            $retryDate = Carbon::parse($retryAfter);

            if ($retryDate->isPast()) {
                return null;
            }

            return (int) now()->diffInMicroseconds($retryDate);
        } catch (InvalidFormatException $e) {
            return null;
        }
    }
}
