<?php

namespace Tests\Feature\MesoDays;

use App\Events\DayToggled;
use App\Listeners\UpdateProgressTargets;
use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DayToggledTest extends TestCase
{
    public static function activationStates(): array
    {
        return [
            'inactive' => [Mesocycle::STATUS_INACTIVE],
            'active' => [Mesocycle::STATUS_ACTIVE],
        ];
    }

    #[DataProvider('activationStates')]
    public function test_finishing_the_last_day_finishes_the_mesocycle_without_changing_activation(int $status): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->notFinished()->create([
            'status' => $status, 'days_per_week' => 1, 'weeks_duration' => 3,
        ]);
        MesoDay::factory()->for($mesocycle)->isFinished()->create(['week' => 1, 'day_order' => 1]);
        MesoDay::factory()->for($mesocycle)->isFinished()->create(['week' => 2, 'day_order' => 1]);
        $day = MesoDay::factory()->for($mesocycle)->create(['week' => 3, 'day_order' => 1]);
        $exercise = DayExercise::factory()->for($day, 'day')->create();
        ExerciseSet::factory()->for($exercise)->create();

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day]))
            ->assertRedirectBack()->assertSessionHasNoErrors();

        $mesocycle->refresh();
        $this->assertNotNull($day->fresh()->finished_at);
        $this->assertNotNull($mesocycle->finished_at);
        $this->assertSame($status, $mesocycle->status);

        $this->get(route('mesocycles'))->assertInertia(fn (Assert $page) => $page
            ->has('mesocycles', 1)
            ->where('mesocycles.0.finished_at', $mesocycle->finished_at));
    }

    #[DataProvider('activationStates')]
    public function test_reopening_a_day_reopens_the_mesocycle_without_changing_activation(int $status): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->isFinished()->create([
            'status' => $status, 'days_per_week' => 1, 'weeks_duration' => 3,
        ]);
        $day = MesoDay::factory()->for($mesocycle)->isFinished()->create(['week' => 3, 'day_order' => 1]);
        $exercise = DayExercise::factory()->for($day, 'day')->create();
        ExerciseSet::factory()->for($exercise)->create(['finished_at' => null]);

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day]))
            ->assertRedirectBack()->assertSessionHasNoErrors();

        $this->assertNull($day->fresh()->finished_at);
        $this->assertNull($mesocycle->fresh()->finished_at);
        $this->assertSame($status, $mesocycle->fresh()->status);
    }

    public function test_finishing_a_day_leaves_the_mesocycle_open_when_another_day_is_unfinished(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->notFinished()->create(['weeks_duration' => 3]);
        $day = MesoDay::factory()->for($mesocycle)->create(['week' => 3]);
        MesoDay::factory()->for($mesocycle)->create(['week' => 2]);

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day]))
            ->assertRedirectBack()->assertSessionHasNoErrors();

        $this->assertNotNull($day->fresh()->finished_at);
        $this->assertNull($mesocycle->fresh()->finished_at);
    }

    public function test_the_event_is_dispatched_on_both_finish_and_reopen(): void
    {
        Event::fake([DayToggled::class]);
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->create();
        $day = MesoDay::factory()->for($mesocycle)->create();

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day]))->assertRedirectBack();
        $this->assertNotNull($day->fresh()->finished_at);
        $this->patch(route('days.toggle', ['day' => $day]))->assertRedirectBack();
        $this->assertNull($day->fresh()->finished_at);

        Event::assertDispatchedTimes(DayToggled::class, 2);
        Event::assertDispatched(DayToggled::class, fn (DayToggled $event): bool => $event->dayId === $day->id);
    }

    public function test_reopened_days_do_not_queue_or_apply_progression_updates(): void
    {
        $mesocycle = Mesocycle::factory()->create(['weeks_duration' => 3]);
        $day = MesoDay::factory()->for($mesocycle)->create(['week' => 1, 'day_order' => 1]);
        $nextDay = MesoDay::factory()->for($mesocycle)->create(['week' => 2, 'day_order' => 1]);
        $exercise = Exercise::query()->firstOrFail();
        $sourceExercise = DayExercise::factory()->for($day, 'day')->for($exercise)->create();
        $nextExercise = DayExercise::factory()->for($nextDay, 'day')->for($exercise)->create();
        ExerciseSet::factory()->for($sourceExercise)->create();
        $target = ExerciseSet::factory()->for($nextExercise)->create();
        $original = $target->fresh()->getAttributes();
        $listener = new UpdateProgressTargets;
        $event = new DayToggled($day->id);

        $this->assertFalse($listener->shouldQueue($event));
        $listener->handle($event);

        $this->assertSame($original, $target->fresh()->getAttributes());
        $this->assertSame(1, $nextExercise->sets()->count());
    }

    public function test_repeated_events_preserve_the_original_mesocycle_completion_time(): void
    {
        $mesocycle = Mesocycle::factory()->isFinished()->create();
        $mesocycle->update(['finished_at' => now()->subDay()]);
        $day = MesoDay::factory()->for($mesocycle)->isFinished()->create(['week' => $mesocycle->weeks_duration]);
        $original = $mesocycle->fresh()->finished_at;

        DayToggled::dispatch($day->id);

        $this->assertSame($original, $mesocycle->fresh()->finished_at);
    }
}
