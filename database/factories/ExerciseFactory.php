<?php

namespace Database\Factories;

use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $exercises = ['Curls', 'Squats', 'Pulldown'];
        $types = ['Dumbbell', 'Barbell', 'Machine'];

        return [
            'name' => fake()->randomElement($exercises),
            'muscle_group_id' => MuscleGroup::factory(),
            'user_id'   => null,
            'youtube_id' => null,
            'exercise_type' => fake()->randomElement($types),
        ];
    }
}
