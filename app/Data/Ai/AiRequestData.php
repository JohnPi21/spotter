<?php

namespace App\Data\Ai;

use App\Enums\RequestStatusEnum;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Ramsey\Uuid\UuidInterface;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
class AiRequestData extends Data
{
    public function __construct(
        public Provider $provider,
        public string $aiModel,
        public int $userId,
        public string $callerClass,
        public string $operationKey,
        public string $promptVersion,
        public string $systemPromptVersion,

        public ?string $schemaVersion,
        public ?string $schemaHash,
        public RequestStatusEnum $status,
        public UuidInterface $requestUuid,
    ) {}

    public static function fromContext(Provider $provider, string $aiModel, AiCallContextData $context): self
    {
        return new self(
            $provider,
            $aiModel,
            $context->userId,
            $context->callerClass,
            $context->operationKey,
            $context->promptVersion,
            $context->systemPromptVersion,
            $context->schemaVersion,
            $context->schemaHash,
            RequestStatusEnum::PENDING,
            Str::uuid(),
        );
    }
}
