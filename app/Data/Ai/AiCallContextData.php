<?php

namespace App\Data\Ai;

use App\Data\AiRequestData;
use Prism\Prism\Contracts\Schema;
use Spatie\LaravelData\Data;

class AiCallContextData extends Data
{
    public function __construct(
        public string $prompt,
        public string $systemPrompt,
        public ?Schema $schema,
        public int $userId,
        public string $callerClass,
        public ?string $schemaVersion,
        public ?string $schemaHash,
        public string $promptVersion,
        public string $systemPromptVersion,
    ) {}
}
