<?php

namespace App\Data\Ai;

use Prism\Prism\Contracts\Schema;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
class AiCallContextData extends Data
{
    public function __construct(
        public string $prompt,
        public string $systemPrompt,
        public ?Schema $schema,
        public int $userId,
        public string $callerClass,
        public string $operationKey,
        public ?string $schemaVersion,
        public ?string $schemaHash,
        public string $promptVersion,
        public string $systemPromptVersion,
    ) {}
}
