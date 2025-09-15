<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DayExercise;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseSet>
 */
class ExerciseSetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_exercise_id' => DayExercise::factory(),
            'reps'            => fake()->numberBetween(3, 30),
            'weight'          => fake()->numberBetween(5, 300),
            'finished_at'     => now()
        ];
    }
}
