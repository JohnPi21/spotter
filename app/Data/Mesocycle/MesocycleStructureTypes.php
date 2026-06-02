<?php

declare(strict_types=1);

namespace App\Data\Mesocycle;

/**
 * @phpstan-type CompletedMesoDay array{id: int, week: int, dayOrder: int}
 * @phpstan-type BuiltMesoDay array{week: int, day_order: int, label: string, position: int}
 * @phpstan-type MesoDayIdMapItem array{week: int, dayOrder: int, mesoDayId: int}
 * @phpstan-type DayExerciseBuild array{meso_day_id: int, exercise_id: int, one_rep_max: int|null, position: int}
 * @phpstan-type DayExerciseMapItem array{dayOrder: int, position: int}
 * @phpstan-type DayExerciseIdMapItem array{dayOrder: int, position: int, dayExerciseId: int, setsCount?: int}
 */
final class MesocycleStructureTypes {}
