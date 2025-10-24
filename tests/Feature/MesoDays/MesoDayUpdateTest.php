<?php

namespace Tests\Feature\MesoDays;

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
        $user = User::factory()->create();
        $meso = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $dayExercise = $meso->days()->first()->dayExercises()->first();

        // Ensure at least 2 finished sets on current day
        // Finish existing sets (if any)
        $dayExercise->sets()->update(['finished_at' => now()]);

        // And add one more finished set explicitly
        ExerciseSet::create([
            'day_exercise_id' => $dayExercise->id,
            'weight' => 50,
            'reps'   => 8,
            'finished_at' => now(),
        ]);

        $day = $dayExercise->day;

        // Ensure next week sibling exists with a matching DayExercise
        $nextDay = $day->nextWeekSibling()->first()
            ?? MesoDay::factory()->for($meso)->create([
                'week' => $day->week + 1,
                'day_order' => $day->day_order,
            ]);

        DayExercise::firstOrCreate(
            ['meso_day_id' => $nextDay->id, 'exercise_id' => $dayExercise->exercise_id],
            ['position' => ($nextDay->dayExercises()->max('position') ?? 0) + 1]
        );

        $nextDayExercise = $nextDay->dayExercises()
            ->where('exercise_id', $dayExercise->exercise_id)
            ->firstOrFail();

        $before = ExerciseSet::where('day_exercise_id', $nextDayExercise->id)->count();

        $this->actingAs($user)
            ->patch(route('days.toggle', ['day' => $day]))
            ->assertRedirectBack()
            ->assertSessionHasNoErrors();

        // Run listener manually (after-commit queue)
        (new UpdateProgressTargets)->handle(new DayFinished($day->id));

        $after = ExerciseSet::where('day_exercise_id', $nextDayExercise->id)->count();

        // EXPECTATION: after equals the number of FINISHED sets on the source day
        $finishedCount = $dayExercise->sets()->whereNotNull('finished_at')->count();

        $this->assertSame(
            $finishedCount,
            $after,
            'Next week should have targets for each finished set of the current day.'
        );

        // If you prefer a looser check, keep:
        // $this->assertGreaterThan($before, $after);
    }
}
