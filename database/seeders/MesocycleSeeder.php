<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;

class MesocycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exerciseIDs = Exercise::pluck('id');

        Mesocycle::factory()
            ->count(20)
            ->create()
            ->each(function ($meso) use ($exerciseIDs) {
                for ($week = 1; $week <= $meso->weeks_duration; $week++) {

                    $days = MesoDay::factory()
                        ->count($meso->days_per_week)
                        ->sequence(function ($sequence) use ($meso, $week) {
                            $dayIndex = $meso->days_per_week - $sequence->index;
                            $offset = ($week - 1) * $meso->days_per_week * 2 + $sequence->index * 2;
                            $date = now()->copy()->subDays($offset);

                            return [
                                "label"         => "Day {$dayIndex}",
                                'mesocycle_id'  => $meso->id,
                                'week'          => $week,
                                'created_at'    => $date,
                                'finished_at'   => $date,
                            ];
                        })
                        ->create();

                    $days->each(function ($day) use ($exerciseIDs) {
                        $dayExercises = DayExercise::factory()
                            ->count(rand(3, 6))
                            ->sequence(fn() => [
                                'meso_day_id' => $day->id,
                                'exercise_id' => $exerciseIDs->random(),
                            ])->create();

                        $dayExercises->each(
                            fn($de) =>
                            ExerciseSet::factory()->count(rand(1, 4))->create([
                                'day_exercise_id' => $de->id
                            ])
                        );
                    });
                }
            });
    }
}
