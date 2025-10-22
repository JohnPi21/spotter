<?php

namespace Tests\Feature\Mesocycles;

use App\Models\Exercise;
use App\Models\Mesocycle;
use App\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;

class MesocycleStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function generateValidPayload(): array
    {
        $exercise = Exercise::factory()->create();
        $muscleGroup = MuscleGroup::factory()->create();

        return [
            'name' => Str::random(32),
            'unit' => 'kg',
            'weeksDuration' => 5,
            'days' => [
                [
                    'label' => 'Day 1',
                    'exercises' => [[
                        'muscleGroup' => $muscleGroup->id,
                        'exerciseID' => $exercise->id
                    ]]
                ],
                [
                    'label' => 'Day 2',
                    'exercises' => [[
                        'muscleGroup' => $muscleGroup->id,
                        'exerciseID' => $exercise->id
                    ]]
                ]
            ]

        ];
    }

    protected function tamperPayload(array $payload): array
    {
        $payload['name'] = 22;
        $payload['days'][0]['label'] = '';
        $payload['days'][0]['exercises'][0]['exerciseID'] = 9999;
        $payload['days'][0]['exercises'][0]['muscleGroup'] = 10000;

        return $payload;
    }


    public function test_it_displays_mesocycle_create()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('mesocycles.create'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('mesocycles/create')
            );
    }

    public function test_it_creates_mesocycles_with_days_and_exercises()
    {
        /**@var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $payload = $this->generateValidPayload();

        $response = $this->actingAs($user)->post(route('mesocycles.store'), $payload);

        $response->assertRedirect(route('mesocycles'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mesocycles', [
            'name' => $payload['name'],
            'weeks_duration' => $payload['weeksDuration'],
        ]);

        $meso = Mesocycle::where('user_id', $user->id)->where('name', $payload['name'])->latest('id')->firstOrFail();

        $this->assertEquals($user->id, $meso->user_id);

        $this->assertDatabaseCount('meso_days', $meso->totalDays());

        $this->assertDatabaseHas('meso_days', ['mesocycle_id' => $meso->id]);

        $this->assertDatabaseCount('day_exercises', ($meso->totalDays()));

        $this->assertDatabaseCount('exercise_sets', ($meso->totalDays()));
    }

    public function test_users_cant_make_mesocycles_for_another_user()
    {
        /**@var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $payload = $this->generateValidPayload();
        $payload['user_id'] = $user->id;

        $this->actingAs($user)->post(route('mesocycles.store'), $payload)->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('mesocycles', [
            'name' => $payload['name'],
            'user_id' => $user->id,
        ]);
    }


    public function test_it_fails_creating_mesocycle_with_wrong_payload()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $payload = $this->tamperPayload($this->generateValidPayload());

        $this->actingAs($user)->post(route('mesocycles.store'), $payload)->assertSessionHasErrors();
    }
}
