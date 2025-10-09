<?php

namespace Tests\Feature\DayExercises;

use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;

class DayExerciseDestroyTest extends TestCase
{
    use RefreshDatabase;


    public function user_can_delete_exercise_from_own_day_and_sets_are_removed()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        $dayExercise = $day->dayExercises()->first();
        // Make sure it has sets (withFullStructure likely did; ensure anyway)
        ExerciseSet::firstOrCreate(['day_exercise_id' => $dayExercise->id]);

        $this->actingAs($user)
            ->delete(route('dayExercise.destroy', [$day, $dayExercise]))
            ->assertRedirect(route('days.show', [$meso, $day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('day_exercises', ['id' => $dayExercise->id]);
        $this->assertDatabaseMissing('exercise_sets', ['day_exercise_id' => $dayExercise->id]);
    }

    public function user_cannot_delete_exercise_from_another_day()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $meso->days->first();
        $dayExercise = $day->dayExercises->first();
        $dayNotContainingExercise = $meso->days()->whereHas('dayExercises', fn($q) => $q->where('id', $dayExercise->id));

        $this->actingAs($user)->delete(route('dayExercise.destroy', [$dayNotContainingExercise, 'dayExercise' => $dayExercise]))->assertRedirectBackWithErrors();

        $this->assertDatabaseHas('day_exercises', ['id' => $dayExercise->id]);
    }


    public function user_cannot_delete_exercise_from_someone_elses_day()
    {
        [$owner, $other] = User::factory()->count(2)->create();
        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $day  = $meso->days()->first();
        $dayExercise = $day->dayExercises()->first();

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($other)
            ->delete(route('dayExercise.destroy', [$day, $dayExercise]))
            ->assertRedirectBackWithErrors('authorization');

        $this->assertDatabaseHas('day_exercises', ['id' => $dayExercise->id]);
    }


    public function user_cannot_delete_when_day_not_editable()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // lock the day
        $day->update(['finished_at' => now()]);

        $dayExercise = $day->dayExercises()->first();

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($user)
            ->delete(route('dayExercise.destroy', [$day, $dayExercise]))
            ->assertRedirectBackWithErrors();

        $this->assertDatabaseHas('day_exercises', ['id' => $dayExercise->id]);
    }
}
