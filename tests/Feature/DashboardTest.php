<?php

namespace Tests\Feature;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-21 12:00:00'));
    }

    private function completedSet(User $user, ?string $finishedAt, ?Exercise $exercise = null, array $attributes = [], ?Mesocycle $mesocycle = null): ExerciseSet
    {
        $mesocycle ??= Mesocycle::factory()->for($user)->isInactive()->create(['unit' => 'kg']);
        $day = MesoDay::factory()->for($mesocycle)->create(['week' => 1, 'day_order' => 1]);
        $dayExercise = DayExercise::factory()->for($day, 'day')->for($exercise ?? Exercise::query()->firstOrFail())->create();

        return ExerciseSet::factory()->for($dayExercise)->create([
            'finished_at' => $finishedAt, 'weight' => 20, 'reps' => 10, ...$attributes,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_empty_dashboard_has_eight_zero_filled_weeks_and_no_invented_data(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
                ->has('training.weeks', 8)->where('training.weeks.7.sets', 0)
                ->where('training.weeks.7.sessions', 0)->where('training.weeks.7.isCurrent', true)
                ->where('training.weeks.7.start', '2026-10-19')
                ->where('training.activePlan', null)->has('training.muscleGroups', 0)
                ->has('training.recentWorkouts', 0)->missing('bestLifts')->missing('activity'));
    }

    public function test_weekly_counts_include_only_this_users_completed_sets_in_the_window(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::query()->firstOrFail();
        $otherExercise = Exercise::query()->where('muscle_group_id', '!=', $exercise->muscle_group_id)->firstOrFail();
        $this->completedSet($user, '2026-10-20 10:00:00', $exercise);
        $this->completedSet($user, '2026-10-21 10:00:00', $exercise);
        $this->completedSet($user, '2026-10-21 11:00:00', $otherExercise, ['weight' => null, 'reps' => null]);
        $this->completedSet($user, null, $exercise);
        $this->completedSet($user, '2026-08-30 23:59:59', $exercise);
        $this->completedSet($user, '2026-10-22 10:00:00', $exercise);
        $this->completedSet(User::factory()->create(), '2026-10-20 10:00:00', $exercise);

        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.weeks.7.sets', 3)->where('training.weeks.7.sessions', 0)
            ->has('training.muscleGroups', 2)
            ->where('training.muscleGroups', function ($groups) use ($exercise, $otherExercise): bool {
                $groups = collect($groups)->keyBy('id');

                return $groups[$exercise->muscle_group_id]['sets'][7] === 2
                    && $groups[$otherExercise->muscle_group_id]['sets'][7] === 1;
            }));
    }

    public function test_monday_boundaries_and_empty_weeks_are_preserved_across_mesocycles(): void
    {
        $user = User::factory()->create();
        foreach (['2026-08-31 00:00:00', '2026-10-18 23:59:59', '2026-10-19 00:00:00', '2026-10-19 01:00:00'] as $date) {
            $this->completedSet($user, $date);
        }
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.weeks.0.start', '2026-08-31')->where('training.weeks.0.sets', 1)
            ->where('training.weeks.1.sets', 0)->where('training.weeks.6.sets', 1)
            ->where('training.weeks.7.sets', 2));
    }

    public function test_sets_are_grouped_by_their_own_completion_date_rather_than_session_date(): void
    {
        $user = User::factory()->create();
        $set = $this->completedSet($user, '2026-10-18 23:59:59');
        $set->dayExercise->day->update(['finished_at' => '2026-10-19 00:01:00']);
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.weeks.6.sets', 1)->where('training.weeks.6.sessions', 0)
            ->where('training.weeks.7.sets', 0)->where('training.weeks.7.sessions', 1));
    }

    public function test_current_plan_is_separate_from_completed_training_and_private_to_its_owner(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::query()->firstOrFail();
        $group = $exercise->muscleGroup;
        $plan = Mesocycle::factory()->for($user)->isActive()->create([
            'name' => 'My weekly routine', 'days_per_week' => 3,
            'planned_stats' => [
                'sets_per_week' => 12, 'muscle_groups_count' => 1,
                'muscle_groups' => [['id' => $group->id, 'name' => $group->name, 'sets' => 12]],
            ],
        ]);
        Mesocycle::factory()->isActive()->create(['name' => 'Someone else']);
        $set = $this->completedSet($user, '2026-10-20 10:00:00', $exercise);
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.activePlan.id', $plan->id)->where('training.activePlan.setsPerWeek', 12)
            ->where('training.weeks.7.sets', 1)->where('training.muscleGroups.0.plannedSets', 12));
        $set->update(['finished_at' => null]);
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.weeks.7.sets', 0)->where('training.muscleGroups.0.plannedSets', 12));
    }

    public function test_missing_plan_stats_are_unavailable_instead_of_becoming_zero_targets(): void
    {
        $user = User::factory()->create();
        Mesocycle::factory()->for($user)->isActive()->create(['planned_stats' => null]);
        $this->completedSet($user, '2026-10-20 10:00:00');
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('training.activePlan.setsPerWeek', null)->where('training.muscleGroups.0.plannedSets', null));
    }

    public function test_recent_workouts_show_completed_sets_per_group_and_secondary_volume_in_the_correct_unit(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->isInactive()->create(['unit' => 'lb']);
        $set = $this->completedSet($user, '2026-10-20 10:00:00', attributes: ['weight' => 50, 'reps' => 8], mesocycle: $mesocycle);
        $day = $set->dayExercise->day;
        $day->update(['finished_at' => '2026-10-20 11:00:00']);
        ExerciseSet::factory()->for($set->dayExercise)->create(['finished_at' => null, 'weight' => 999, 'reps' => 99]);
        $future = $this->completedSet($user, '2026-10-22 10:00:00');
        $future->dayExercise->day->update(['finished_at' => '2026-10-22 10:00:00']);
        $foreign = $this->completedSet(User::factory()->create(), '2026-10-21 10:00:00');
        $foreign->dayExercise->day->update(['finished_at' => '2026-10-21 10:00:00']);
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->has('training.recentWorkouts', 1)->where('training.recentWorkouts.0.id', $day->id)
            ->where('training.recentWorkouts.0.sets', 1)->where('training.recentWorkouts.0.exercises', 1)
            ->where('training.recentWorkouts.0.muscleGroups.0.sets', 1)
            ->where('training.recentWorkouts.0.load', 50)->where('training.recentWorkouts.0.volume', 400)
            ->where('training.recentWorkouts.0.unit', 'lb'));
    }

    public function test_recent_workouts_are_the_latest_three_rather_than_the_first_three(): void
    {
        $user = User::factory()->create();
        $days = [];
        foreach ([1, 2, 3, 4] as $day) {
            $set = $this->completedSet($user, "2026-10-{$day} 10:00:00");
            $set->dayExercise->day->update(['finished_at' => "2026-10-{$day} 11:00:00"]);
            $days[] = $set->dayExercise->day->id;
        }
        $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->has('training.recentWorkouts', 3)->where('training.recentWorkouts.0.id', $days[3])
            ->where('training.recentWorkouts.2.id', $days[1]));
    }
}
