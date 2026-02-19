<?php

namespace App\Data;

use App\Data\Ai\AiCallContextData;
use App\Enums\AiRequestEnum;
use App\Enums\RequestStatusEnum;
use Spatie\LaravelData\Data;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;
use Prism\Prism\Enums\Provider;

class AiRequestData extends Data
{
    public function __construct(
        public Provider $provider,
        public string $aImodel,
        public int $userId,
        public string $callerClass,
        public string $promptVersion,
        public string $systemPromptVersion,

        public ?string $schemaVersion,
        public ?string $schemaHash,
        public RequestStatusEnum $status = RequestStatusEnum::PENDING,
        public UuidInterface $requestUuid = Str::uuid(),
    ) {}

    public static function fromContext(Provider $provider, string $aiModel, AiCallContextData $context): self
    {
        return new self(
            $provider,
            $aiModel,
            $context->userId,
            $context->callerClass,
            $context->promptVersion,
            $context->systemPromptVersion,
            $context?->schemaVersion,
            $context?->schemaHash,
        );
    }
}
