<?php

namespace Tests\Feature;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

class MesoDayShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_day(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $mesocycle->days->first();


        $this->actingAs($user)->get(route('days.show', ['mesocycle' => $mesocycle->id, 'day' => $day->id]))
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('mesocycles/show')
                    ->has('mesocycle')
                    ->where('mesocycle.id', $mesocycle->id)
                    ->where('mesocycle.day.id', $day->id)
            );
    }

    public function test_day_shows_recommendations_from_previous_exercises(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->create();

        $days = MesoDay::factory()->for($mesocycle)->count($mesocycle->totalDays())->sequence(fn($sequence) => [
            'day_order' => $sequence->index % $mesocycle->days_per_week + 1,
            'week'      => intdiv($sequence->index, $mesocycle->weeks_duration) + 1
        ])->create();

        $exercisesID = Exercise::select('id')->limit(2)->pluck('id');

        foreach ($days as $day) {
            $dayExercise = DayExercise::factory()->for($day, 'day')->sequence(fn($sequence) => [
                'exercise_id' => $exercisesID[$sequence->index]
            ])->count(2)->create();

            $dayExercise->each(function ($dayEx) {
                ExerciseSet::factory()->for($dayEx, 'dayExercise')->count(3)->create();
            });
        }

        $firstDayOfSecondWeek = $mesocycle->days->get($mesocycle->days_per_week);

        $this->actingAs($user)->get(route('days.show', ['mesocycle' => $mesocycle->id, 'day' => $firstDayOfSecondWeek->id]))
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('mesocycles/show')
                    ->has('mesocycle')
                    ->where('mesocycle.id', $mesocycle->id)
                    ->where('mesocycle.day.id', $firstDayOfSecondWeek->id)
                    ->has('mesocycle.day.day_exercises.0.sets.0.target_reps')
                    ->has('mesocycle.day.day_exercises.0.sets.0.target_weight')
            );
    }

    public function test_user_can_see_calendar(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $mesocycle->days->first();


        $this->actingAs($user)->get(route('days.show', ['mesocycle' => $mesocycle->id, 'day' => $day->id]))
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('mesocycles/show')
                    ->has('mesocycle')
                    ->where('mesocycle.id', $mesocycle->id)
                    ->where('mesocycle.day.id', $day->id)
                    ->has('mesocycle.calendar')
            );
    }

    public function test_user_cannot_see_another_users_days(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $otherUser */
        $otherUser = User::factory()->create();

        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();
        $day = $mesocycle->days->first();


        $this->actingAs($otherUser)->get(route('days.show', ['mesocycle' => $mesocycle->id, 'day' => $day->id]))->assertForbidden();
    }
}
