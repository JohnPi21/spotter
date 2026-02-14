<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Data\AiRequestData;
use App\Data\AiRequestDTOData;
use App\Enums\AiRequestEnum;
use App\Models\AiRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Text\PendingRequest;
use App\Enums\RequestStatusEnum;
use Illuminate\Support\Str;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Exceptions\PrismServerException;

class PrismAiClient implements AiClient
{
    public Provider $provider;
    public string $model;

    public function __construct()
    {
        $this->provider = Provider::from(config('ai.default_provider')) ?? Provider::OpenAI;
        $this->model    = config('ai.default_model');
    }

    public function text(string $prompt, string $systemPrompt): string
    {
        $response = Prism::text()
            ->using($this->provider, $this->model)
            ->withPrompt($prompt)
            ->withSystemPrompt($systemPrompt)
            ->onComplete(
                fn(PendingRequest $request, Collection $messages) =>
                dd($request, $messages)
                // $this->conversationLog(AiRequestEnum::TEXT, $request)
            )
            ->asText();

        return $response->text;
    }


    // CHECK Plan AI request logging strategy for strategy. Scroll 2 prompts up
    public function structured(string $prompt, string $systemPrompt, Schema $schema): array
    {
        // @TODO: move to DTO later
        // $aiRequestDTO = $this->aiRequestDtoFactory(AiRequestEnum::STRUCTURED);

        // AiRequest::create($aiRequestDTO->toArray());

        $aiRequest = AiRequest::create([
            'request_uuid' => Str::uuid(),
            'type'      => AiRequestEnum::STRUCTURED,
            'provider'  => $this->provider,
            'model'     => $this->model,
            'status'    => RequestStatusEnum::PENDING
        ]);

        $start = microtime(true);
        try {
            $response = Prism::structured()
                ->using($this->provider, $this->model)
                ->withPrompt($prompt)
                ->withSystemPrompt($systemPrompt)
                ->withSchema($schema)
                ->withClientOptions([
                    'schema' => [
                        'strict' => true
                    ]
                ]);
            // ->asStructured();
        } catch (PrismException $e) {

            // @TODO: Set failed send extra data??? make logging inside model?? create the logger here? what to log exactly;

            $aiRequest->setFailed();
        } catch (\Throwable $th) {
            $aiRequest->update([
                'status' => RequestStatusEnum::FAILED,
            ]);
        }

        // @TODO: complete success path
        $time = microtime(true) - $start;

        $aiRequest->fill([
            'latency_ms' => $time,
        ]);


        Log::channel('openai')->info("$this->provider request", [
            'type'      => AiRequestEnum::STRUCTURED,
            'model'     => $this->model,
            'payload'   => [$prompt, $systemPrompt, $schema],
            'response'  => $response,
        ]);

        // $this->conversationLog(AiRequestEnum::STRUCTURED, [$prompt, $systemPrompt, $schema], $response);
        // dd($response);

        // return $response->structured;
        return [];
        return $response;
    }

    public function chat() {}


    public function aiRequestDtoFactory(AiRequestEnum $type): AiRequestData
    {
        return new AiRequestData($this->provider, $this->model, $type);
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
