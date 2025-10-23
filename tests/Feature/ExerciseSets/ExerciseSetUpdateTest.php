<?php

namespace Tests\Feature\ExerciseSets;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mesocycle;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use App\Models\DayExercise;

class ExerciseSetUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_set_and_mark_finished()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patch(route('sets.update', [$dayExercise, $set]), [
                'reps'   => 10,
                'weight' => '100',
                'status' => 1, // finished
            ])
            ->assertRedirect(route('days.show', [$dayExercise->day->mesocycle, $dayExercise->day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exercise_sets', [
            'id'         => $set->id,
            'reps'       => 10,
            'weight'     => 100,
        ]);

        $this->assertNotNull($set->fresh()->finished_at);
    }

    public function test_user_can_update_own_set_and_mark_unfinished()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patch(route('sets.update', [$dayExercise, $set]), [
                'reps'   => 8,
                'weight' => '60',
                'status' => 0, // unfinished
            ])
            ->assertRedirect(route('days.show', [$dayExercise->day->mesocycle, $dayExercise->day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exercise_sets', [
            'id'     => $set->id,
            'reps'   => 8,
            'weight' => 60,
        ]);
        $this->assertNull($set->fresh()->finished_at);
    }

    public function test_update_without_status_marks_finished_by_current_logic()
    {
        // NOTE: Controller sets finished_at = now() if status is omitted.
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patch(route('sets.update', [$dayExercise, $set]), [
                'reps'   => 5,
                'weight' => '50',
                // no status
            ])
            ->assertRedirect(route('days.show', [$dayExercise->day->mesocycle, $dayExercise->day]))
            ->assertSessionHasNoErrors();

        $this->assertNotNull($set->fresh()->finished_at);
    }

    public function test_update_fails_validation_for_bad_payload()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patch(route('sets.update', [$dayExercise, $set]), [
                'reps'   => 999,  // > 64 (invalid)
                'weight' => '500,000000', // > 8 (invalid)
                'status' => 2,    // not in 0,1
            ])
            ->assertRedirect() // back to form
            ->assertSessionHasErrors(['reps', 'weight', 'status']);
    }

    public function test_user_cannot_update_someone_elses_set()
    {
        [$owner, $other] = User::factory()->count(2)->create();

        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($other)
            ->patch(route('sets.update', [$dayExercise, $set]), [
                'reps'   => 6,
                'weight' => '70',
                'status' => 1,
            ])
            ->assertRedirectBackWithErrors();

        // unchanged
        $this->assertDatabaseMissing('exercise_sets', [
            'id' => $set->id,
            'reps' => 6,
            'weight' => 70,
        ]);
    }
}
