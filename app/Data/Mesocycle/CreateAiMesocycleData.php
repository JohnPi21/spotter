<?php

namespace App\Data\Mesocycle;

use App\Enums\ExperienceEnum;
use App\Enums\SessionDurationEnum;
use App\Enums\SplitsEnum;
use App\Enums\TrainingGoalsEnum;
use App\Enums\UnitsOfMeasure;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateAiMesocycleData extends Data
{
    /*
    *   @var EquipmentEnum[] $equipment;
    */
    public function __construct(
        public string $name,
        public UnitsOfMeasure $unit,
        public int $weeksDuration,
        public int $daysPerWeek,
        public SessionDurationEnum $sessionDuration,
        public TrainingGoalsEnum $primaryGoal,
        public SplitsEnum $splitPreference,
        public ExperienceEnum $experienceLevel,
        public array $equipment,
        public string $injuries,
        public string $notes
    ) {}
}
