<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\{Mesocycle, MesoDay, DayExercise, ExerciseSet, User};

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'              => 'Demo',
            'email'             => 'demo-' . Str::random(8) . '@spotacus.com',
            'email_verified_at' => now(),
            'password'          => Hash::make(Str::random(16)),
            'remember_token'    => Str::random(10),
            'created_at'        => now()->subWeeks(5),
        ]);

        $user->assignRole(RolesEnum::GUEST);

        foreach (range(1, $user->id) as $userID) {
            $meso = Mesocycle::create([
                'name' => 'Upper/Lower Hypertrophy',
                'unit' => 'kg',
                'days_per_week' => 4,
                'weeks_duration' => 6,
                'user_id' => $userID,
                'status' => \App\Models\Mesocycle::STATUS_ACTIVE,
                'started_at' => now()->subWeeks(5),
                'finished_at' => now(),
            ]);

            $exercises = [
                'Day 1' => [1, 26, 53, 130],
                'Day 2' => [197, 159, 44, 78],
                'Day 3' => [1, 26, 53, 130],
                'Day 4' => [197, 159, 44, 78]
            ];

            $dayLabels = ['Day 1', 'Day 2', 'Day 3', 'Day 4'];

            for ($week = 1; $week <= $meso->weeks_duration; $week++) {

                foreach ($dayLabels as $idx => $label) {
                    $day = MesoDay::create([
                        'label' => $label,
                        'mesocycle_id' => $meso->id,
                        'week' => $week,
                        'day_order' => $idx + 1,
                        'position'  => $idx,
                        'created_at' => now()->subWeeks(6 - $week),
                        'finished_at' => now()->subWeeks(6 - $week)->subDays(count($dayLabels) - $idx),
                    ]);

                    foreach ($exercises[$label] as $position => $exerciseID) {
                        $dayExercise = DayExercise::create([
                            'meso_day_id' => $day->id,
                            'exercise_id' => $exerciseID,
                            'position' => $position
                        ]);

                        $isBigLift = in_array($exerciseID, [1, 197]); // e.g., Squat/Bench
                        for ($i = 1; $i <= 3; $i++) {
                            $baseWeight = $isBigLift ? rand(50, 100) : rand(10, 50);
                            $reps = $baseWeight > 50 ? rand(3, 6) : rand(6, 12);

                            ExerciseSet::create([
                                'day_exercise_id' => $dayExercise->id,
                                'weight' => $baseWeight + rand(-5, 5),
                                'reps' => $reps
                            ]);
                        }
                    }
                }
            }
        }
    }
}
