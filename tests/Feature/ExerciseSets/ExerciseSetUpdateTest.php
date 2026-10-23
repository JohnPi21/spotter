<?php

namespace Tests\Feature\ExerciseSets;

use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseSetUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_only_update_requires_reps_and_weight(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();
        $original = $set->getAttributes();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), ['min_rir' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reps', 'weight']);

        $this->assertSame($original, $set->fresh()->getAttributes());
    }

    public function test_performance_update_does_not_change_targets(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();
        $targets = ['min_reps' => 6, 'max_reps' => 12, 'min_rir' => 1, 'max_rir' => 3];
        $set->update($targets);

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 8,
                'weight' => '60',
                'status' => true,
                'min_reps' => 2,
                'max_reps' => 4,
                'min_rir' => 0,
                'max_rir' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('set.reps', 8)
            ->assertJsonPath('set.weight', '60.00');

        $this->assertDatabaseHas('exercise_sets', ['id' => $set->id, ...$targets]);
    }

    public function test_json_update_returns_only_the_saved_set(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 3,
                'weight' => '25',
                'status' => true,
            ])
            ->assertOk()
            ->assertJsonPath('set.id', $set->id)
            ->assertJsonPath('set.reps', 3)
            ->assertJsonPath('set.weight', '25.00')
            ->assertJsonMissingPath('set.day_exercise')
            ->assertJsonMissingPath('mesocycle');

        $this->assertNotNull($set->fresh()->finished_at);
    }

    public function test_json_update_returns_validation_errors_without_changing_the_set(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();
        $original = $set->getAttributes();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 999,
                'weight' => 'invalid',
                'status' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reps', 'weight']);

        $this->assertSame($original, $set->fresh()->getAttributes());
    }

    public function test_json_update_cannot_modify_another_users_set(): void
    {
        [$owner, $other] = User::factory()->count(2)->create();
        $mesocycle = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $dayExercise = $mesocycle->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();
        $original = $set->getAttributes();

        $this->actingAs($other)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 3,
                'weight' => '25',
                'status' => true,
            ])
            ->assertForbidden();

        $this->assertSame($original, $set->fresh()->getAttributes());
    }

    public function test_user_can_update_own_set_and_mark_finished(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 10,
                'weight' => '100',
                'status' => 1, // finished
            ])
            ->assertOk();

        $this->assertDatabaseHas('exercise_sets', [
            'id' => $set->id,
            'reps' => 10,
            'weight' => 100,
        ]);

        $this->assertNotNull($set->fresh()->finished_at);
    }

    public function test_user_can_update_own_set_and_mark_unfinished(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 8,
                'weight' => '60',
                'status' => 0, // unfinished
            ])
            ->assertOk();

        $this->assertDatabaseHas('exercise_sets', [
            'id' => $set->id,
            'reps' => 8,
            'weight' => 60,
        ]);
        $this->assertNull($set->fresh()->finished_at);
    }

    public function test_update_without_status_marks_finished_by_current_logic(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 5,
                'weight' => '50',
                // no status
            ])
            ->assertOk();

        $this->assertNotNull($set->fresh()->finished_at);
    }

    public function test_update_fails_validation_for_bad_payload(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($user)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 999,  // > 64 (invalid)
                'weight' => '500,000000', // > 8 (invalid)
                'status' => 2,    // not in 0,1
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reps', 'weight', 'status']);
    }

    public function test_user_cannot_update_someone_elses_set(): void
    {
        [$owner, $other] = User::factory()->count(2)->create();

        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $set = $dayExercise->sets()->first();

        $this->actingAs($other)
            ->patchJson(route('sets.update', [$dayExercise, $set]), [
                'reps' => 6,
                'weight' => '70',
                'status' => 1,
            ])
            ->assertForbidden();

        // unchanged
        $this->assertDatabaseMissing('exercise_sets', [
            'id' => $set->id,
            'reps' => 6,
            'weight' => 70,
        ]);
    }
}
