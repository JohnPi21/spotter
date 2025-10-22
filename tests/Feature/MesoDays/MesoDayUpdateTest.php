<?php

namespace Tests\Feature;

use App\Events\DayFinished;
use App\Listeners\UpdateProgressTargets;
use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MesoDayUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_day(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $mesocycle->days->first();

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day->id]))
            ->assertRedirectBack();

        $this->assertIsObject((MesoDay::find($day->id))->finished_at);
    }

    public function test_user_cannot_finish_day_with_unfinished_sets(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $day = $mesocycle->days->first();

        $set = $day->dayExercises->first()->sets->first();
        $set->update(['finished_at' => null]);

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day->id]))->assertSessionHasErrors();

        $this->assertNull((MesoDay::find($day->id))->finished_at);
        $this->assertDatabaseHas('meso_days', ['id' => $day->id, 'finished_at' => null]);
    }

    public function test_user_cannot_toggle_other_users_days(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $otherUser */
        $otherUser = User::factory()->create();
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $mesocycle->days->first();

        $this->actingAs($otherUser)->patch(route('days.toggle', ['day' => $day->id]))->assertRedirectBackWithErrors();

        $this->assertNull((MesoDay::find($day->id))->finished_at);
    }

    public function test_targets_for_next_week_update_on_day_finish()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        // Current day/exercise + ensure there are at least 2 sets
        $dayExercise = $meso->days()->first()->dayExercises()->first();
        $currentSet = $dayExercise->sets()->first();
        ExerciseSet::create(['day_exercise_id' => $dayExercise->id, 'finished_at' => now()]); // now count > 1

        // Find or create next week's same weekday
        $day = $dayExercise->day;
        $nextDay = $day->nextWeekSibling()->first();

        if (!$nextDay) {
            $nextDay = MesoDay::factory()->for($meso)->create([
                'week' => $day->week + 1,
                'day_order' => $day->day_order,
            ]);
        }

        // Ensure a DayExercise exists on next day for the same exercise
        $exExists = DayExercise::where('meso_day_id', $nextDay->id)->where('exercise_id', $dayExercise->exercise_id)->exists();

        if (! $exExists) {
            $nextPos = $nextDay->dayExercises()->max('position') + 1;
            $nextDayExercise = DayExercise::factory()
                ->for($nextDay, 'day')
                ->exercise($dayExercise->exercise_id)
                ->state(['position' => $nextPos])
                ->create();
        }

        $nextDayExercise = $nextDay->dayExercises()->first();

        $before = ExerciseSet::where('day_exercise_id', $nextDayExercise->id)->count();

        $this->actingAs($user)
            ->patch(route('days.toggle', ['day' => $day]))
            // ->assertRedirect(route('days.show', ['mesocycle' => $day->mesocycle_id, 'day' => $day->id]))
            ->assertRedirectBack()
            ->assertSessionHasNoErrors();

        // Run this manually because tests cannot run after commit queues
        (new UpdateProgressTargets)->handle(new DayFinished($day->id));

        $after = ExerciseSet::where('day_exercise_id', $nextDayExercise->id)->count();
        $this->assertGreaterThan($before, $after, 'Expected a new set to be created on next week same exercise.');
    }
}
