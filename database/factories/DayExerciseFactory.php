<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MesoDay;
use App\Models\Exercise;

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

        $exerciseIDs = Exercise::pluck('id');

        return [
            'meso_day_id' => MesoDay::factory(),
            'exercise_id' => $exerciseIDs->random(),
            'position'    => 1,
        ];
    }

    public function exercise(int $exerciseID)
    {
        return $this->state(fn() => ['exercise_id' => $exerciseID]);
    }
}
