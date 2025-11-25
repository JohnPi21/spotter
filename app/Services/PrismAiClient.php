<?php

namespace App\Services;

use App\Contracts\AiClient;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class PrismAiClient implements AiClient
{
    // params: config & prompt
    public function text()
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt');
    }

    public function structured() {}

    public function chat() {}
}
