<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MuscleGroup>
 */
class MuscleGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $muscleGroups = ['Chest', 'Back', 'Triceps', 'Biceps', 'Shoulders', 'Quads', 'Glutes', 'Hamstrings', 'Calves', 'Traps', 'Forearms', 'Abs'];

        return [
            'name' => fake()->randomElement($muscleGroups),
        ];
    }
}
