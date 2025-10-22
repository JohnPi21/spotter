<?php

namespace Database\Factories;

use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Exercise;
use App\Models\MesoDay;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Sequence;
use RuntimeException;

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
        $names = ['Beginner', 'Superman', 'Hell for noobs', 'Upper Focus', 'Lower Focus', 'Accessory focus', 'Upper Lower', 'Bro Split', 'High reps', 'Hypertrophy', 'Power', 'Maintenance', 'Compounds'];

        return [
            'name'              => fake()->randomElement($names),
            'unit'              => 'kg',
            'days_per_week'     => fake()->numberBetween(1, 7),
            'weeks_duration'    => fake()->numberBetween(...Mesocycle::weeksRange()),
            'user_id'           => User::factory(),
            'notes'             => null,
            'status'            => fake()->numberBetween(Mesocycle::STATUS_INACTIVE, Mesocycle::STATUS_ACTIVE),
            'meso_template_id'  => null,
            'started_at'        => fake()->dateTimeBetween('-1 month', 'now'),
            'finished_at'       => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Build a full structure: days, day_exercises & exercise_sets.
     *
     * @param int $exercisesPerDay  how many exercises per day
     * @param int $setsPerExercise  how many sets per exercise
     * @param ?callable $exercisePicker fn(): Exercise  // optional custom picker
     */
    public function withFullStructure(
        int $exercisesPerDay = 5,
        int $setsPerExercise = 3,
        ?callable $exercisePicker = null
    ): static {
        return $this->afterCreating(function ($meso) use ($exercisesPerDay, $setsPerExercise, $exercisePicker) {

            // Ensure we have exercises to attach
            if (Exercise::count() === 0 || MuscleGroup::count() === 0) {
                throw new RuntimeException('Run exercise & muscle groups seeders');
            }

            MesoDay::factory()
                ->for($meso)
                ->count($meso->totalDays())
                ->create()
                ->each(function (MesoDay $day) use ($exercisesPerDay, $setsPerExercise, $exercisePicker) {
                    // Pick exercises (random by default, or via custom picker)
                    $exerciseIds = collect(range(1, $exercisesPerDay))->map(function () use ($exercisePicker) {
                        return optional($exercisePicker ? $exercisePicker() : Exercise::inRandomOrder()->first())->id;
                    })->filter()->unique()->values();

                    // Create DayExercise rows for those exercises
                    $dayExercises = collect($exerciseIds)->map(function ($exerciseId, $i) use ($day) {
                        return DayExercise::factory()->for($day, 'day')->for(Exercise::find($exerciseId))->state(['position' => $i + 1])->create();
                    });

                    // Create sets for each day exercise
                    $dayExercises->each(function (DayExercise $de) use ($setsPerExercise) {
                        ExerciseSet::factory()->for($de)->count($setsPerExercise)->create();
                    });
                });
        });
    }

    public function isFinished()
    {
        return $this->state(function (array $attributes) {
            return ['finished_at' => now()];
        });
    }

    public function notFinished()
    {
        return $this->state(function (array $attributes) {
            return ['finished_at' => null];
        });
    }

    public function isActive()
    {
        return $this->state(fn() => ['status' => Mesocycle::STATUS_ACTIVE]);
    }

    public function isInactive()
    {
        return $this->state(fn() => ['status' => Mesocycle::STATUS_INACTIVE]);
    }
}
