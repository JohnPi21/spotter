<?php

namespace Tests\Feature\Mesocycles;

use App\Actions\Mesocycle\MesocycleToText;
use App\Models\Mesocycle;
use App\Models\User;
use Mockery\MockInterface;
use Tests\TestCase;

class MesocycleExportTest extends TestCase
{
    public function test_owner_can_export_mesocycle_as_text(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for($user)->withFullStructure()->create();

        $this->mock(MesocycleToText::class, function (MockInterface $mock) use ($mesocycle) {
            $mock->shouldReceive('execute')
                ->once()
                ->withArgs(fn (Mesocycle $argument) => $argument->is($mesocycle))
                ->andReturn('exported mesocycle');
        });

        /** @var \Illuminate\Contracts\Auth\Authenticatable $user* */
        $this->actingAs($user)
            ->getJson(route('mesocycles.copy', $mesocycle))
            ->assertOk()
            ->assertJsonFragment([
                'text' => 'exported mesocycle',
            ]);
    }

    public function test_user_cannot_export_another_users_mesocycle(): void
    {
        $user = User::factory()->create();
        $mesocycle = Mesocycle::factory()->for(User::factory())->create();

        $this->mock(MesocycleToText::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('execute');
        });

        /** @var \Illuminate\Contracts\Auth\Authenticatable $user* */
        $this->actingAs($user)
            ->getJson(route('mesocycles.copy', $mesocycle))
            ->assertForbidden();
    }
}
