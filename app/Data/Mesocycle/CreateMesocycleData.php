<?php

namespace App\Data\Mesocycle;

use App\Enums\UnitsOfMeasure;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class CreateMesocycleData extends Data
{
    /**
     * @param  array<int, \App\Data\Mesocycle\DayTemplateData>  $days
     */
    public function __construct(
        public string $name,
        public UnitsOfMeasure|Optional $unit,
        public int $weeksDuration,
        public array $days,
    ) {}
}
