<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Data\Ai\AiCallContextData;
use App\Data\Ai\AiRequestData;
use App\Enums\RequestStatusEnum;
use App\Exceptions\AppException;
use App\Models\AiRequest;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use App\Services\RateLimiting\RateLimitRetrier;
use Exception;
use Illuminate\Support\Facades\Http;
use stdClass;
use Throwable;

class PrismAiClient implements AiClient
{
	public Provider $provider;

	public string $model;

	public function __construct(private RateLimitRetrier $retrier)
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

		Http::retry();

		try {
			throw new PrismRateLimitedException([], 50);
			// throw new PrismException('F up', 503);
			$response = $this->retrier->retry(
				fn() => $this->buildStructuredRequest($aiCallContext),
				when: fn(Throwable $e) => $e instanceof PrismRateLimitedException,
				retryAfterCallback: function (Throwable $e) {
					if ($e instanceof PrismRateLimitedException) return $e->retryAfter;
				}
			);
		} catch (Throwable $e) {
			// TODO: Set failed send extra data??? log data / error here
			$aiRequest->setFailed($e, 'TRANSPORT');

			throw $e;
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
			])
			->asStructured();

		$response = $this->mockupResponse();

		return $response;
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
