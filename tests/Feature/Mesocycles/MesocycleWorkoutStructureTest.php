<?php

namespace Tests\Feature\Mesocycles;

use App\Models\Mesocycle;
use App\Models\User;
use Tests\TestCase;

class MesocycleWorkoutStructureTest extends TestCase
{
    public function test_the_scope_loads_the_nested_workout_structure_and_composes_with_other_scopes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Mesocycle::factory()->create();
        $mesocycle = Mesocycle::factory()->withFullStructure(2, 2)->create([
            'user_id' => $user->id,
            'days_per_week' => 1,
            'weeks_duration' => 3,
        ]);

        $mesocycles = Mesocycle::query()->mine()->withWorkoutStructure()->orderByDesc('status')->get();

        $this->assertCount(1, $mesocycles);
        $this->assertTrue($mesocycle->is($mesocycles->first()));
        $mesocycle = $mesocycles->first();

        $this->assertTrue($mesocycle->relationLoaded('days'));
        $this->assertCount(3, $mesocycle->days);

        foreach ($mesocycle->days as $day) {
            $this->assertTrue($day->relationLoaded('dayExercises'));
            $this->assertNotEmpty($day->dayExercises);

            foreach ($day->dayExercises as $dayExercise) {
                $this->assertTrue($dayExercise->relationLoaded('exercise'));
                $this->assertTrue($dayExercise->exercise->relationLoaded('muscleGroup'));
                $this->assertNotNull($dayExercise->exercise->muscleGroup);
                $this->assertTrue($dayExercise->relationLoaded('sets'));
                $this->assertCount(2, $dayExercise->sets);
            }
        }

    }

    public function test_it_handles_a_mesocycle_without_days(): void
    {
        $mesocycle = Mesocycle::factory()->create();

        $mesocycle = Mesocycle::query()->withWorkoutStructure()->findOrFail($mesocycle->id);

        $this->assertTrue($mesocycle->relationLoaded('days'));
        $this->assertEmpty($mesocycle->days);
    }
}
