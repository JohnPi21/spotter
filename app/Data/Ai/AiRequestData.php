<?php

namespace App\Data;

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
        public string $model,
        public AiRequestEnum $type,
        public RequestStatusEnum $status = RequestStatusEnum::PENDING,
        public UuidInterface $request_uuid = Str::uuid(),
    ) {}
}
