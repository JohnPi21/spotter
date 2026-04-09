<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

class ExerciseTemplateData extends Data
{
    /**
     * @param  array<int, SetTemplateData[]>  $sets
     */
    public function __construct(
        #[MapInputName(CamelCaseMapper::class)]
        public int $muscleGroup,
        public int $exerciseID,
        public ?array $sets,
    ) {}
}
