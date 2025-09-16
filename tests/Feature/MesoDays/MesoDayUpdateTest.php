<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Mesocycle;
use App\Models\MesoDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\ExerciseSet;

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
        $day->update(['finished_at' => null]);

        $set = $day->dayExercises->first()->sets->first();
        $set->update(['finished_at' => null]);

        $this->actingAs($user)->patch(route('days.toggle', ['day' => $day->id]))->assertSessionHasErrors();

        $this->assertNull((MesoDay::find($day->id))->finished_at);
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
}
