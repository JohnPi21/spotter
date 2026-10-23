<?php

namespace Database\Seeders;

use App\Actions\Mesocycle\CreateMesocycle;
use App\Data\Mesocycle\CreateMesocycleData;
use App\Data\Mesocycle\DayTemplateData;
use App\Data\Mesocycle\ExerciseTemplateData;
use App\Data\Mesocycle\SetTemplateData;
use App\Enums\RolesEnum;
use App\Enums\UnitsOfMeasure;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminMesocycleSeeder extends Seeder
{
    public function run(CreateMesocycle $createMesocycle): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $admin = User::role(RolesEnum::ADMIN)->orderBy('id')->first();

        if (! $admin) {
            $this->command?->warn('No admin user found. Run UserSeeder first.');

            return;
        }

        $plans = [
            ['name' => 'Playground: Full Body', 'weeks' => 4, 'days' => [
                'Full Body A' => ['Barbell Squat (Close Stance, Feet Forward)', 'Bench Press (Medium Grip)', 'Barbell Flexion Row'],
                'Full Body B' => ['Barbell Hip Thrust', 'Machine Shoulder Press', 'Hanging Knee Raise'],
                'Full Body C' => ['Reverse Lunge (Dumbbell)', 'Dumbbell Press (Flat)', 'Barbell Flexion Row'],
            ]],
            ['name' => 'Playground: Upper / Lower', 'weeks' => 6, 'days' => [
                'Upper A' => ['Bench Press (Medium Grip)', 'Barbell Flexion Row', 'Machine Shoulder Press'],
                'Lower A' => ['Barbell Squat (Close Stance, Feet Forward)', 'Barbell Hip Thrust', 'Hanging Knee Raise'],
                'Upper B' => ['Dumbbell Press (Flat)', 'Barbell Flexion Row', 'Cable Rope Twist Curl'],
                'Lower B' => ['Reverse Lunge (Dumbbell)', 'Barbell Hip Thrust', 'Machine Crunch'],
            ]],
            ['name' => 'Playground: Short Sessions', 'weeks' => 3, 'days' => [
                'Session A' => ['Barbell Squat (Close Stance, Feet Forward)', 'Bench Press (Medium Grip)'],
                'Session B' => ['Barbell Hip Thrust', 'Barbell Flexion Row'],
            ]],
        ];

        $exercises = Exercise::query()->get()->keyBy('name');

        DB::transaction(function () use ($plans, $exercises, $admin, $createMesocycle): void {
            foreach ($plans as $plan) {
                if ($admin->mesocycles()->where('name', $plan['name'])->exists()) {
                    continue;
                }

                $days = [];

                foreach ($plan['days'] as $label => $exerciseNames) {
                    $dayExercises = [];

                    foreach ($exerciseNames as $name) {
                        $exercise = $exercises->get($name);

                        if (! $exercise) {
                            throw new RuntimeException("Missing exercise: {$name}. Run the exercise catalog seeders first.");
                        }

                        $dayExercises[] = new ExerciseTemplateData(
                            muscleGroup: $exercise->muscle_group_id,
                            exerciseId: $exercise->id,
                            oneRepMax: null,
                            sets: array_map(
                                fn (int $index): SetTemplateData => new SetTemplateData(8, 12, 1, 3, null),
                                range(1, 3),
                            ),
                        );
                    }

                    $days[] = new DayTemplateData($label, $dayExercises);
                }

                $createMesocycle->execute(
                    new CreateMesocycleData($plan['name'], UnitsOfMeasure::KG, $plan['weeks'], $days),
                    $admin->id,
                    $admin->mesocycles()->active()->exists(),
                );
            }
        });
    }
}
