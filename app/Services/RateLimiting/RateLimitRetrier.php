<?php

namespace App\Services\RateLimiting;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Http\Client\ConnectionException;

class RateLimitRetrier
{
    /**
     * @var array<int, int>
     */
    private array $retryCodes = [408, 429, 500, 502, 503, 504];

    /**
     * Retry a callback with backoff
     *
     * @param callable $callback
     * @param integer $maxAttempts
     * @param integer $maxSecondsToWait
     * @return mixed
     */
    public function retry(callable $callback, int $maxAttempts = 3, int $maxSecondsToWait = 96): mixed
    {
        // 1 < $maxAttempts < 10;
        // 7 attempts => 128 seconds at most wait time
        $maxAttempts = min(max($maxAttempts, 1), 7);
        $maxMicroToWait = $maxSecondsToWait * 1_000_000;
        $lastException = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                return $callback();
            } catch (ConnectionException $e) {
                $lastException = $e;
                $timeToWait = $this->exponentialRetry($attempt);

                if ($timeToWait < $maxMicroToWait) {
                    usleep($timeToWait);
                    continue;
                }

                throw $e;
            } catch (RequestException $e) {
                $lastException = $e;
                $response = $e->response;
                $code = $response->status();

                $retryAfterMicro = null;

                if (! in_array($code, $this->retryCodes, true)) {
                    throw $e;
                }

                $retryAfterMicro = $this->getRetryAfterHeaderInMicro($response);

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

        throw $lastException;
    }

    protected function exponentialRetry(int $attempt): int
    {
        $jitter = random_int(50_000, 250_000);
        return ((2 ** $attempt) * 1_000_000) + $jitter;
    }

    /**
     * Get Retry-After header value in microseconds if available
     *
     * @param \Illuminate\Http\Client\Response|null $response
     * @return integer|null
     */
    public function getRetryAfterHeaderInMicro(?\Illuminate\Http\Client\Response $response): ?int
    {
        if (! $response) {
            return null;
        }

        $retryAfter = $response->header('Retry-After');

        if (is_numeric($retryAfter) && $retryAfter > 0) return (int) $retryAfter * 1_000_000;

        try {
            $retryDate = Carbon::parse($retryAfter);

            if ($retryDate->isPast()) return null;

            return (int) now()->diffInMicroseconds($retryDate);
        } catch (InvalidFormatException $e) {
            return null;
        }
    }
}
