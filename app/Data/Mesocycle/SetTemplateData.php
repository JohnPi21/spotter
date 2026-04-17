<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Data;

class SetTemplateData extends Data
{
    public function __construct(
        public ?int $minReps,
        public ?int $maxReps,
        public ?int $minRir,
        public ?int $maxRir,
        public ?int $percentOneRepMax,
    ) {}
}
