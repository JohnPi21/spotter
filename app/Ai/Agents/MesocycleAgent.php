<?php

namespace App\Ai\Agents;

use App\Contracts\AiClient;

class MesocycleAgent
{

    // This uses PrismAiClient to make the requests to the ai
    // Here is the dirty work

    public function __construct(public AiClient $aiClient) {}

    public function generate()
    {
        // Use aiClient to generate to my needs
    }
};
