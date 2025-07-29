<?php

namespace Database\Factories;

use App\Models\Mesocycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MesoDay>
 */
class MesoDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notes = json_encode([
            'title'     => fake()->sentence(3),
            'tags'      => fake()->words(3),
            'comment'   => fake()->paragraph(),
        ]);

        return [
            'mesocycle_id'  => Mesocycle::factory(),
            'week'          => fake()->numberBetween(Mesocycle::MIN_WEEKS, Mesocycle::MAX_WEEKS),
            'day_order'     => fake()->numberBetween(1, 7),
            'body_weight' => fake()->boolean(80)
                ? fake()->numberBetween(30, 150)
                : null,
            'label'         => fake()->word(),
            'position'      => fake()->numberBetween(1, 7),
            'notes'         => $notes,
            'status'        => fake()->numberBetween(0, 1),
        ];
    }


    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 1
            ];
        });
    }
}
