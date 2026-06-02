<?php

namespace App\Data\Mesocycle;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class SetTemplateData extends Data
{
    public function __construct(
        public ?int $minReps,
        public ?int $maxReps,
        public ?int $minRir,
        public ?int $maxRir,
        public ?int $percentOneRepMax,
    ) {}

    public static function emptyPayload(): array
    {
        return [
            'min_reps' => null,
            'max_reps' => null,
            'min_rir' => null,
            'max_rir' => null,
            'percent_one_rep_max' => null,
        ];
    }
}
