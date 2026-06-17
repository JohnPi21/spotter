<?php

namespace Tests\Unit\Actions;

use App\Actions\Mesocycle\MesocycleToText;
use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\MuscleGroup;
use PHPUnit\Framework\TestCase;

class MesocycleToTextTest extends TestCase
{
    public function test_it_converts_a_mesocycle_to_text(): void
    {
        $exercise = (new Exercise)->forceFill([
            'name' => 'Bench Press',
            'exercise_type' => 'barbell',
        ]);
        $exercise->setRelation('muscleGroup', (new MuscleGroup)->forceFill(['name' => 'Chest']));

        $set = new ExerciseSet([
            'weight' => 100,
            'reps' => 8,
            'min_reps' => 6,
            'max_reps' => 10,
        ]);

        $dayExercise = new DayExercise;
        $dayExercise->setRelation('exercise', $exercise);
        $dayExercise->setRelation('sets', collect([$set]));

        $day = new MesoDay([
            'label' => 'Push Day',
            'week' => 1,
        ]);
        $day->setRelation('dayExercises', collect([$dayExercise]));

        $mesocycle = new Mesocycle(['unit' => 'kg']);
        $mesocycle->setRelation('days', collect([$day]));

        $expected = "Week 1\n\nPush Day\n\nChest \n"
            ."Bench Press - barbell \n"
            ."- Set 1: 100.00 kg x 8 reps | Target Reps: 6 - 10 \n\n";

        $this->assertSame($expected, (new MesocycleToText)->execute($mesocycle));
    }
}
