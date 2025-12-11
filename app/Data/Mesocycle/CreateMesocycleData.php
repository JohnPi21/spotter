<?php

namespace App\Data\Mesocycle;

use App\Enums\UnitsOfMeasure;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

class CreateMesocycleData extends Data
{
    /**
     * @param array<int, \App\Data\Mesocycle\DayTemplateData> $days
     */
    public function __construct(
        public string $name,
        public UnitsOfMeasure|Optional $unit,
        #[MapInputName(CamelCaseMapper::class)]
        public int $weeks_duration,
        public array $days,
    ) {}
}
