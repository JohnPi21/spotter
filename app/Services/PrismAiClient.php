<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Data\Ai\AiCallContextData;
use App\Data\Ai\AiRequestData;
use App\Enums\RequestStatusEnum;
use App\Exceptions\AppException;
use App\Models\AiRequest;
use App\Services\RateLimiting\RateLimitRetrier;
use Illuminate\Support\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest;
use Throwable;

class PrismAiClient implements AiClient
{
    public Provider $provider;

    public string $model;

    public function __construct(private RateLimitRetrier $retrier)
    {
        $this->provider = Provider::from(config('ai.default_provider', Provider::OpenAI));
        $this->model = config('ai.default_model');
    }

    public function text(AiCallContextData $context): string
    {
        $response = Prism::text()
            ->using($this->provider, $this->model)
            ->withPrompt($context->prompt)
            ->withSystemPrompt($context->systemPrompt)
            ->onComplete(
                fn (PendingRequest $request, Collection $messages) => dd($request, $messages)
                // $this->conversationLog(AiRequestEnum::TEXT, $request)
            )
            ->asText();

        return $response->text;
    }

    /**
     * @return array{AiRequest, array}
     */
    public function structured(AiCallContextData $aiCallContext): array
    {
        $payload = AiRequestData::fromContext($this->provider, $this->model, $aiCallContext);

        $aiRequest = AiRequest::create($payload->toArray());

        $start = hrtime(true);

        try {
            $response = $this->retrier->run(
                function ($attempt) use ($aiCallContext) {
                    return $this->buildStructuredRequest($aiCallContext);
                },
                3,
                when: fn (Throwable $e) => $e instanceof PrismRateLimitedException,
                retryAfterCallback: function (Throwable $e) {
                    if ($e instanceof PrismRateLimitedException) {
                        return $e->retryAfter;
                    }
                }
            );
        } catch (Throwable $e) {
            $aiRequest->setFailed($e, 'TRANSPORT');

            throw new AppException(502, 'AI Provider is not available', 'AI_PROVIDER_UNAVAILABLE', $e);
        }
        $latencyMs = intdiv(hrtime(true) - $start, 1_000_000);

        $usage = $response['usage'];
        $aiRequest->fill([
            'latency_ms' => $latencyMs,
            'usage_json' => json_encode($usage),
            'prompt_tokens' => $usage['promptTokens'],
            'completion_tokens' => $usage['completionTokens'],
            'total_tokens' => $usage['promptTokens'] + $usage['completionTokens'],
            'finish_reason' => $response['finishReason'],
            'status' => RequestStatusEnum::SUCCESS,
            'meta_id' => $response['meta']['id'],
        ])->save();

        $aiRequest->refresh();

        return [$aiRequest, $response];
    }

    public function chat() {}

    private function buildStructuredRequest(AiCallContextData $context): array
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

    public function mockupResponse(): array
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
        { "muscleGroup": 1, "exerciseId": 26 },
        { "muscleGroup": 1, "exerciseId": 44 },
        { "muscleGroup": 5, "exerciseId": 157 },
        { "muscleGroup": 5, "exerciseId": 130 },
        { "muscleGroup": 3, "exerciseId": 89 },
        { "muscleGroup": 3, "exerciseId": 93 }
      ]
    },
    {
      "label": "Pull",
      "exercises": [
        { "muscleGroup": 2, "exerciseId": 53 },
        { "muscleGroup": 2, "exerciseId": 56 },
        { "muscleGroup": 2, "exerciseId": 78 },
        { "muscleGroup": 4, "exerciseId": 105 },
        { "muscleGroup": 4, "exerciseId": 109 },
        { "muscleGroup": 10, "exerciseId": 164 }
      ]
    },
    {
      "label": "Legs",
      "exercises": [
        { "muscleGroup": 6, "exerciseId": 1 },
        { "muscleGroup": 6, "exerciseId": 188 },
        { "muscleGroup": 6, "exerciseId": 189 },
        { "muscleGroup": 7, "exerciseId": 197 },
        { "muscleGroup": 8, "exerciseId": 213 },
        { "muscleGroup": 9, "exerciseId": 216 }
      ]
    },
    {
      "label": "Upper",
      "exercises": [
        { "muscleGroup": 1, "exerciseId": 26 },
        { "muscleGroup": 2, "exerciseId": 53 },
        { "muscleGroup": 1, "exerciseId": 44 },
        { "muscleGroup": 2, "exerciseId": 78 },
        { "muscleGroup": 5, "exerciseId": 157 },
        { "muscleGroup": 3, "exerciseId": 101 },
        { "muscleGroup": 4, "exerciseId": 105 },
        { "muscleGroup": 10, "exerciseId": 178 }
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
}',
            true
        );
    }
}
