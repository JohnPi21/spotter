<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Enums\AiRequestEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Text\PendingRequest;

class PrismAiClient implements AiClient
{
    public ?Provider $provider;
    public ?string $model;

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


    public function structured(string $prompt, string $systemPrompt, Schema $schema): array
    {
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

        // $this->conversationLog(AiRequestEnum::STRUCTURED, [$prompt, $systemPrompt, $schema], $response);
        // dd($response);

        // return $response->structured;
        return $response;
    }

    public function chat() {}

    private function conversationLog(AiRequestEnum $type, $payload, $response): void
    {
        Log::channel('openai')->info("$this->provider request", [
            'type'      => $type,
            'model'     => $this->model,
            'payload'   => $payload,
            'response'  => $response,
        ]);
    }
}
