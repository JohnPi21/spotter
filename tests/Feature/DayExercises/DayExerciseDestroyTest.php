<?php

namespace Tests\Feature\DayExercises;

use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayExerciseDestroyTest extends TestCase
{
    use RefreshDatabase;


    public function user_can_delete_exercise_from_own_day_and_sets_are_removed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        $exercise = $day->dayExercises()->first();
        // Make sure it has sets (withFullStructure likely did; ensure anyway)
        ExerciseSet::firstOrCreate(['day_exercise_id' => $exercise->id]);

        $this->actingAs($user)
            ->delete(route('dayExercise.destroy', [$day, $exercise]))
            ->assertRedirect(route('days.show', [$meso, $day]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('day_exercises', ['id' => $exercise->id]);
        $this->assertDatabaseMissing('exercise_sets', ['day_exercise_id' => $exercise->id]);
    }


    public function user_cannot_delete_exercise_from_someone_elses_day()
    {
        [$owner, $other] = User::factory()->count(2)->create();
        $meso = Mesocycle::factory()->for($owner)->withFullStructure()->create();
        $day  = $meso->days()->first();
        $exercise = $day->dayExercises()->first();

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($other)
            ->delete(route('dayExercise.destroy', [$day, $exercise]))
            ->assertRedirectBackWithErrors('authorization');

        $this->assertDatabaseHas('day_exercises', ['id' => $exercise->id]);
    }


    public function user_cannot_delete_when_day_not_editable()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day  = $meso->days()->first();

        // lock the day
        $day->update(['finished_at' => now()]);

        $exercise = $day->dayExercises()->first();

        $this->from(route('days.show', [$meso, $day]))
            ->actingAs($user)
            ->delete(route('dayExercise.destroy', [$day, $exercise]))
            ->assertRedirectBackWithErrors();

        $this->assertDatabaseHas('day_exercises', ['id' => $exercise->id]);
    }
}
