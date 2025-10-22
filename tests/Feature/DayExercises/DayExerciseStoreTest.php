<?php

namespace Tests\Feature\DayExercises;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DayExerciseStoreTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_add_exercise_to_own_day_and_gets_initial_set()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // pick an exercise not already on this day
        $existingExerciseIds = DayExercise::where('meso_day_id', $day->id)->pluck('exercise_id');
        $exerciseId = Exercise::whereNotIn('id', $existingExerciseIds)->value('id');
        $this->assertNotNull($exerciseId, 'Need at least one exercise in DB that is not already on the day');

        $lastPos = DayExercise::where('meso_day_id', $day->id)->max('position') ?? -1;
        $exercisesCount = count($existingExerciseIds);

        $this->actingAs($user)
            ->post(route('dayExercises.store', $day), ['exercise_id' => $exerciseId])
            ->assertRedirectToRoute('days.show', [$meso, $day])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->get(route('days.show', [$meso, $day]))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->where('mesocycle.day.day_exercises', fn($items) => collect($items)->contains('exercise_id', $exerciseId))
            );

        $created = DayExercise::where('meso_day_id', $day->id)->where('exercise_id', $exerciseId)->first();

        $this->assertNotNull($created);
        $this->assertSame($lastPos + 1, $created->position);

        // a first set is auto-created
        $this->assertDatabaseHas('exercise_sets', ['day_exercise_id' => $created->id]);
    }


    public function test_user_cannot_add_duplicate_exercise_on_same_day()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        $existingExerciseId = DayExercise::where('meso_day_id', $day->id)->value('exercise_id');

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($user)
            ->post(route('dayExercises.store', $day), ['exercise_id' => $existingExerciseId])
            ->assertRedirectBack()
            ->assertSessionHasErrors(['exercise_id']);
    }


    public function test_user_cannot_add_exercise_to_someone_elses_day()
    {
        [$owner, $other] = User::factory()->count(2)->create();
        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $day  = $meso->days()->first();

        $exerciseId = Exercise::value('id');

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($other)
            ->post(route('dayExercises.store', $day), ['exercise_id' => $exerciseId])
            ->assertRedirectBackWithErrors();

        // unchanged
        $this->assertDatabaseMissing('day_exercises', [
            'meso_day_id'  => $day->id,
            'exercise_id'  => $exerciseId,
        ]);
    }


    public function test_user_cannot_add_exercise_when_day_not_editable()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // Simulate finished / locked day so ensureIsEditable() fails
        $day->update(['finished_at' => now()]);

        $exerciseId = Exercise::value('id');

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($user)
            ->post(route('dayExercises.store', $day), ['exercise_id' => $exerciseId])
            ->assertRedirectBackWithErrors();
    }
}
