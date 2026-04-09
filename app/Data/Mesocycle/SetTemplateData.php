<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Data;

class SetTemplateData extends Data
{
    public function __construct(
        public int $reps,
        public int $oneRepMaxPercent
    ) {}
}
