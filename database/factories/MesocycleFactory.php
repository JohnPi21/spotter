<?php

namespace Database\Factories;

use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mesocycle>
 */
class MesocycleFactory extends Factory
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

        $names = ['Beginner', 'Superman', 'Hell for noobs', 'Upper Focus', 'Lower Focus', 'Accessory focus', 'Upper Lower', 'Bro Split', 'High reps', 'Hypertrophy', 'Power', 'Maintenance', 'Compounds'];

        $userIDs = User::pluck('id');

        return [
            'name'              => fake()->randomElement($names),
            'unit'              => 'kg',
            'days_per_week'     => fake()->numberBetween(1, 7),
            'weeks_duration'    => fake()->numberBetween(...Mesocycle::weeksRange()),
            'user_id'           => fake()->randomElement($userIDs),
            'notes'             => $notes,
            'status'            => fake()->numberBetween(Mesocycle::STATUS_INACTIVE, Mesocycle::STATUS_ACTIVE),
            'meso_template_id'  => null,
            'started_at'        => fake()->dateTimeBetween('-1 month', 'now'),
            'finished_at'       => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
