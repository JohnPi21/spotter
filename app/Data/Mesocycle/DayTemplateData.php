<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Data;

class DayTemplateData extends Data
{
    /**
     * @param  ExerciseTemplateData[]  $exercises
     */
    public function __construct(
        public string $label,
        public array $exercises,
    ) {}
}
