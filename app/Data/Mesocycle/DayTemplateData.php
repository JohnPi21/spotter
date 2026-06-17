<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Data;

class DayTemplateData extends Data
{
    /**
     * @param  array<int, ExerciseTemplateData>  $exercises
     */
    public function __construct(
        public string $label,
        public array $exercises,
    ) {}
}
