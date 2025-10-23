<?php

namespace Tests\Feature\Mesocycles;

use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use Tests\TestCase;
use App\Models\User;

class MesocycleDestroyTest extends TestCase
{
    public function test_user_can_delete_mesocycle()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $mesocycle->load('days.dayExercises.sets');

        $this->actingAs($user)
            ->delete(route('mesocycles.destroy', ['mesocycle' => $mesocycle->id]))
            ->assertRedirectToRoute('mesocycles')
            ->assertSessionDoesntHaveErrors();

        $this->assertModelMissing($mesocycle);
        $this->assertDatabaseMissing('meso_days', ['mesocycle_id' => $mesocycle->id]);

        // $this->assertTrue(
        //     MesoDay::whereKey($dayIds)->doesntExist(),
        //     'Some meso_days still exist'
        // );

        $this->assertTrue(DayExercise::whereIn('meso_day_id', $mesocycle->days->pluck('id'))->doesntExist());
        $this->assertTrue(ExerciseSet::whereIn('day_exercise_id', $mesocycle->days->flatMap->dayExercises->pluck('id'))->doesntExist());
    }

    public function test_user_cannot_delete_other_users_mesocycle()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($owner)->create();

        $this->actingAs($user)->delete(route('mesocycles.destroy', ['mesocycle' => $mesocycle->id]))
            ->assertStatus(302)
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('mesocycles', ['id' => $mesocycle->id]);
    }
}
