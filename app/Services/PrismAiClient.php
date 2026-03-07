<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Data\Ai\AiCallContextData;
use App\Data\Ai\AiRequestData;
use App\Enums\RequestStatusEnum;
use App\Exceptions\AppException;
use App\Models\AiRequest;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use stdClass;

class PrismAiClient implements AiClient
{
    public Provider $provider;

    public string $model;

    public function __construct()
    {
        $this->provider = Provider::from(config('ai.default_provider')) ?? Provider::OpenAI;
        $this->model = config('ai.default_model');
    }

    public function text(AiCallContextData $context): string
    {
        $response = Prism::text()
            ->using($this->provider, $this->model)
            ->withPrompt($context->prompt)
            ->withSystemPrompt($context->systemPrompt)
            ->onComplete(
                fn(PendingRequest $request, Collection $messages) => dd($request, $messages)
                // $this->conversationLog(AiRequestEnum::TEXT, $request)
            )
            ->asText();

        return $response->text;
    }



    public function structured(AiCallContextData $aiCallContext): array
    {
        $payload = AiRequestData::fromContext($this->provider, $this->model, $aiCallContext);

        $aiRequest = AiRequest::create($payload->toArray());

        $start = hrtime(true);
        try {
            $response = $this->buildStructuredRequest($aiCallContext);

            throw new PrismException('F up');
        } catch (PrismRateLimitedException $e) {
            // TODO: impelent retry

        } catch (PrismException $e) {
            // TODO: Set failed send extra data??? log data / error here
            // stop the request here with an exception
            // Implement retry in case of 429 too many requests
            $aiRequest->setFailed($e, 'transport');
            throw new AppException(500, 'Ai');
        }
        //@TODO: complete success path
        $latencyMs = intdiv(hrtime(true) - $start, 1_000_000);

        $usage = $response->usage;
        $aiRequest->fill([
            'latency_ms' => $latencyMs,
            'usage_json' => json_encode($usage),
            'prompt_tokens' => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'total_tokens' => $usage->promptTokens + $usage->completionTokens,
            'finish_reason' => $response->finishReason,
            'status' => RequestStatusEnum::SUCCESS,
            'meta_id' => $response->meta->id,
        ])->save();

        dd($aiRequest->refresh());

        // @TODO: test the response, handle the errors and retries
        // Divide schema validation from within the schema and Domain logic validation
        return [$aiRequest, $response];
    }

    public function chat() {}

    private function buildStructuredRequest(AiCallContextData $context): stdClass
    {
        $response = Prism::structured()
            ->using($this->provider, $this->model)
            ->withPrompt($context->prompt)
            ->withSystemPrompt($context->systemPrompt)
            ->withSchema($context->schema)
            ->withClientOptions([
                'schema' => [
                    'strict' => true,
                ],
            ]);
        // ->asStructured();

        $response = $this->mockupResponse();

        return $response;
    }

    // TODO: implement an api endpoint that returns RateLimit with header so i can test this.
    private function retryAfterRateLimit(callable $callback, int $maxAttempts = 3, int $attempt = 1)
    {
        if ($attempt > $maxAttempts) {
            throw AppException::tooManyRequests();
        }

        try {
            return $callback();
        } catch (RequestException $e) {
            $retryAfterSeconds = null;

            if ($e->getResponse()?->getStatusCode() == 429) {
                $retryAfterHeader = $e->getResponse()?->getHeader('Retry-After');

                // TODO: learn about HTTP retry after header
                // TODO Also implement date
                if (isset($retryAfterHeader[0]) && is_numeric($retryAfterHeader[0])) {
                    $retryAfterSeconds = (int) $retryAfterHeader[0];
                }
            } else {
                throw new AppException();
            }

            sleep($this->calculateBackoffSeconds($attempt, $retryAfterSeconds));

            return $this->retryAfterRateLimit($callback, $maxAttempts, ++$attempt);
        }
    }

    private function calculateBackoffSeconds(int $attempt, ?int $retryAfterSeconds = null): int
    {
        if ($retryAfterSeconds) return $retryAfterSeconds;

        return min($attempt ** 2, 10);
    }

    public function mockupResponse()
    {
        return json_decode(
            '{
  "name": "4-week Strength/Hypertrophy Mesocycle",
  "unit": "kg",
  "weeksDuration": 4,
  "days": [
    {
      "label": "Push",
      "exercises": [
        { "muscleGroup": 1, "exerciseID": 101 },
        { "muscleGroup": 1, "exerciseID": 102 },
        { "muscleGroup": 2, "exerciseID": 201 },
        { "muscleGroup": 2, "exerciseID": 202 },
        { "muscleGroup": 3, "exerciseID": 301 },
        { "muscleGroup": 3, "exerciseID": 302 }
      ]
    },
    {
      "label": "Pull",
      "exercises": [
        { "muscleGroup": 4, "exerciseID": 401 },
        { "muscleGroup": 4, "exerciseID": 402 },
        { "muscleGroup": 4, "exerciseID": 403 },
        { "muscleGroup": 2, "exerciseID": 203 },
        { "muscleGroup": 5, "exerciseID": 501 },
        { "muscleGroup": 5, "exerciseID": 502 }
      ]
    },
    {
      "label": "Legs",
      "exercises": [
        { "muscleGroup": 6, "exerciseID": 601 },
        { "muscleGroup": 6, "exerciseID": 602 },
        { "muscleGroup": 7, "exerciseID": 701 },
        { "muscleGroup": 6, "exerciseID": 603 },
        { "muscleGroup": 8, "exerciseID": 801 },
        { "muscleGroup": 9, "exerciseID": 901 }
      ]
    },
    {
      "label": "Upper",
      "exercises": [
        { "muscleGroup": 1, "exerciseID": 103 },
        { "muscleGroup": 4, "exerciseID": 404 },
        { "muscleGroup": 2, "exerciseID": 204 },
        { "muscleGroup": 4, "exerciseID": 405 },
        { "muscleGroup": 3, "exerciseID": 303 },
        { "muscleGroup": 5, "exerciseID": 503 },
        { "muscleGroup": 10, "exerciseID": 1001 },
        { "muscleGroup": 10, "exerciseID": 1002 }
      ]
    }
  ],
  "finishReason": "stop",
  "usage": {
    "promptTokens": 243,
    "completionTokens": 1532,
    "thoughtTokens": 960,
    "cacheReadInputTokens": 0,
    "cacheWriteInputTokens": null
  },
  "meta": {
    "id": "resp_05b924ac0055891300692ddcec693481a19428b06d4ee96219",
    "model": "gpt-5-mini-2025-08-07",
    "rateLimits": {}
  },
  "toolCalls": [],
  "toolResults": [],
  "additionalContent": []
}'
        );
    }
}
