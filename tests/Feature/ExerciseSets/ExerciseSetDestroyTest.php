<?php

namespace Tests\Feature;

use App\Models\ExerciseSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mesocycle;
use App\Models\MesoDay;

class ExerciseSetDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_destroy_own_set()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $countBefore = ExerciseSet::where('day_exercise_id', $dayExercise->id)->count();

        $this->actingAs($user)
            ->delete(route('sets.destroy', ['set' => $set->id]))
            ->assertRedirectBack()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('exercise_sets', ['id' => $set->id]);
        $this->assertSame($countBefore - 1, ExerciseSet::where('day_exercise_id', $dayExercise->id)->count());
    }

    public function test_user_cannot_destroy_someone_elses_set()
    {
        [$owner, $other] = User::factory()->count(2)->create();

        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $countBefore = ExerciseSet::where('day_exercise_id', $dayExercise->id)->count();

        $this->actingAs($other)
            ->delete(route('sets.destroy', ['set' => $set->id]))
            ->assertRedirectBackWithErrors();

        $this->assertDatabaseHas('exercise_sets', ['id' => $set->id]);
        $this->assertSame($countBefore, ExerciseSet::where('day_exercise_id', $dayExercise->id)->count());
    }
}
