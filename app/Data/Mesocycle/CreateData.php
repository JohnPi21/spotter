<?php

namespace App\Data\Mesocycle;

use App\Enums\UnitOfMeasure;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

class CreateData extends Data
{
    /**
     * @param array<int, \App\Data\Mesocycle\DayTemplateData> $days
     */
    public function __construct(
        public string $name,
        public UnitOfMeasure|Optional $unit,
        #[MapInputName(CamelCaseMapper::class)]
        public int $weeks_duration,
        public array $days,
    ) {}
}
