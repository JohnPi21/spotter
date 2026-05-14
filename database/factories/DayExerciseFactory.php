<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\MesoDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DayExercise>
 */
class DayExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $exerciseIds = Exercise::pluck('id');

        return [
            'meso_day_id' => MesoDay::factory(),
            'exercise_id' => $exerciseIds->random(),
            'position' => 1,
        ];
    }

    public function exercise(int $exerciseId)
    {
        return $this->state(fn () => ['exercise_id' => $exerciseId]);
    }
}
