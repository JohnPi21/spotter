<?php

namespace Tests\Feature;

use App\Models\DayExercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mesocycle;
use App\Models\ExerciseSet;

class ExerciseSetStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_set()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $setsCount = $dayExercise->sets->count();

        $this->actingAs($user)->post(route('sets.store'), ['day_exercise_id' => $dayExercise->id])
            ->assertRedirectBack()
            ->assertSessionHasNoErrors();

        $this->assertEquals(ExerciseSet::where('day_exercise_id', $dayExercise->id)->count(), ($setsCount + 1));
    }

    public function test_user_cannot_create_set_for_inexistent_day()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        Mesocycle::factory()->for($user)->withFullStructure()->create();
        $lastDayEx = DayExercise::orderByDesc('id')->first();

        $this->actingAs($user)->post(route('sets.store'), ['day_exercise_id' => ($lastDayEx->id + 1)])
            ->assertRedirectBack()
            ->assertSessionHasErrors();
    }

    public function test_user_cannot_create_set_for_another_user()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $otherUser */
        $otherUser = User::factory()->create();
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $setsCount = $dayExercise->sets->count();

        $this->actingAs($otherUser)->post(route('sets.store'), ['day_exercise_id' => $dayExercise->id])
            ->assertRedirectBack()
            ->assertSessionHasErrors();

        $this->assertEquals(ExerciseSet::where('day_exercise_id', $dayExercise->id)->count(), $setsCount);
    }
}
