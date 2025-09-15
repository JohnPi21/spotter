<?php

namespace Tests\Feature;

use App\Models\Mesocycle;
use App\Models\MesoDay;
use App\Models\User;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MesocycleIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can access the mesocycle index page when authenticated.
     */
    public function test_it_displays_index_page_to_authenticated_users(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->component('mesocycles/index');
            });
    }

    /**
     * Guests cannot see this page
     */
    public function test_it_redirects_guests_to_login(): void
    {
        $response = $this->get(route('mesocycles'));

        $response->assertRedirect(route('login'));
    }


    public function test_it_allows_user_not_have_mesocycles(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->has('mesocycles');
            });
    }


    public function test_it_displays_only_mesocycles_owned_by_user()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        Mesocycle::factory()->count(3)->for(User::factory())->create();

        $ownedMesosCount = Mesocycle::factory()->count(2)->for($user)->create()->count();

        $props = $this->actingAs($user, 'web')->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(fn($page) => $page->has('mesocycles', $ownedMesosCount))
            ->inertiaProps();

        $mesocycles = collect($props['mesocycles']);

        $this->assertTrue($mesocycles->every('user_id', '===', $user->id));
    }

    public function test_it_presents_last_unfinished_day_if_meso_unfinished()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $meso = Mesocycle::factory()->for($user)->create();

        MesoDay::factory()
            ->count($meso->totalDays() - 1)
            ->for($meso)
            ->isFinished()
            ->create();

        MesoDay::factory()->for($meso)->create();

        $meso->refresh();

        $this->actingAs($user, 'web')
            ->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('mesocycles', 1)
                    ->has(
                        'mesocycles.0',
                        fn(Assert $m) => $m
                            ->has('last_day')
                            ->where('last_day', $meso->last_day)
                            ->etc()
                    )

            );
    }

    public function test_it_presents_last_day_if_meso_finished()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $meso = Mesocycle::factory()->for($user)->isFinished()->create();

        MesoDay::factory()
            ->count($meso->totalDays())
            ->for($meso)
            ->isFinished()
            ->create();

        $meso->refresh();

        $this->actingAs($user, 'web')
            ->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('mesocycles', 1)
                    ->has(
                        'mesocycles.0',
                        fn(Assert $m) => $m
                            ->has('last_day')
                            ->where('last_day', $meso->last_day)
                            ->etc()
                    )

            );
    }

    public function test_it_presents_last_day_last_unfinished_day_or_last_day_in_mesocycle()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $finishedMeso = Mesocycle::factory()->for($user)->create();
        $unfinishedMeso = Mesocycle::factory()->notFinished()->for($user)->create();

        MesoDay::factory()
            ->count($finishedMeso->totalDays())
            ->isFinished()
            ->for($finishedMeso)
            ->create();

        MesoDay::factory()
            ->count($unfinishedMeso->totalDays() - 1)
            ->isFinished()
            ->for($unfinishedMeso)
            ->create();

        MesoDay::factory()->for($unfinishedMeso)->create();


        $this->actingAs($user, 'web')
            ->get(route('mesocycles'))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($finishedMeso, $unfinishedMeso) {
                $mesoProps = collect($page->toArray()['props']['mesocycles'])->keyBy('id');

                $this->assertEquals($mesoProps[$finishedMeso->id]['last_day'], $finishedMeso->last_day);
                $this->assertEquals($mesoProps[$unfinishedMeso->id]['last_day'], $unfinishedMeso->last_day);
            });
    }


    public function test_redirects_to_current_day_on_active_mesocycle(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->notFinished()->isActive()->create();

        MesoDay::factory()->for($mesocycle)->isFinished()->count($mesocycle->totalDays() - 1)->create();

        MesoDay::factory()->for($mesocycle)->create();

        $currentDayId = $mesocycle->days()->whereNull('finished_at')->orderBy('id')->value('id');

        $currentDayId ??= $mesocycle->days()->orderByDesc('id')->value('id');

        $this->actingAs($user)
            ->get(route('mesocycles.current'))
            ->assertRedirectToRoute('days.show', ['mesocycle' => $mesocycle->id, 'day' => $currentDayId])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_active_finished_mesocycle_redirects_to_last_day(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->isActive()->isFinished()->create();

        MesoDay::factory()->for($mesocycle)->isFinished()->count($mesocycle->totalDays())->create();

        $currentDayId = $mesocycle->days()->whereNull('finished_at')->orderBy('id')->value('id');

        $currentDayId ??= $mesocycle->days()->orderByDesc('id')->value('id');

        $this->actingAs($user)
            ->get(route('mesocycles.current'))
            ->assertRedirectToRoute('days.show', ['mesocycle' => $mesocycle->id, 'day' => $currentDayId])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_inactive_mesocycle_active_day_404(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $mesocycle = Mesocycle::factory()->for($user)->isFinished()->isInactive()->create();

        MesoDay::factory()->for($mesocycle)->isFinished()->count($mesocycle->totalDays())->create();

        $this->actingAs($user)
            ->get(route('mesocycles.current'))
            ->assertNotFound();
    }
}
