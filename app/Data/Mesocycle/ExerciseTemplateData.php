<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class ExerciseTemplateData extends Data
{
    /**
     * @param  array<int, SetTemplateData>  $sets
     */
    public function __construct(
        public int $muscleGroup,
        public int $exerciseId,
        public ?int $oneRepMax,
        public array $sets = [],
    ) {}
}
