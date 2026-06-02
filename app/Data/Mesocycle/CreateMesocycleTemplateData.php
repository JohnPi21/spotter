<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Data;
use stdClass;

class CreateMesocycleTemplateData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public int $frequency,
        public string|stdClass $schema,
        public bool $ai_generated,
        public ?int $user_id,
        public ?int $ai_request_id
    ) {}
}
