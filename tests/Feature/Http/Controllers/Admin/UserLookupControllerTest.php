<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\TestCase;

class UserLookupControllerTest extends TestCase
{
    public function test_admin_can_load_user_options_without_a_search_term(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        User::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.users.lookup'))
            ->assertOk()
            ->assertJsonCount(2, 'users.data');
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        $nameMatch = User::factory()->create([
            'name' => 'Needle Alpha',
            'email' => 'alpha@example.com',
        ]);
        $emailMatch = User::factory()->create([
            'name' => 'Beta User',
            'email' => 'needle@example.com',
        ]);
        User::factory()->create([
            'name' => 'Unrelated User',
            'email' => 'unrelated@example.com',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.users.lookup', [
            'search' => 'needle',
        ]));

        $response->assertOk();
        $this->assertSame([
            [
                'id' => $emailMatch->id,
                'name' => $emailMatch->name,
                'email' => $emailMatch->email,
            ],
            [
                'id' => $nameMatch->id,
                'name' => $nameMatch->name,
                'email' => $nameMatch->email,
            ],
        ], $response->json('users.data'));
    }

    public function test_lookup_pagination_preserves_the_search_term(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $admin = User::factory()->create();
        $admin->assignRole(RolesEnum::ADMIN->value);
        User::factory()->count(16)->create([
            'name' => 'Matching User',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.users.lookup', [
            'search' => 'Matching',
        ]));

        $response->assertOk()->assertJsonCount(15, 'users.data');
        $this->assertStringContainsString('search=Matching', $response->json('users.next_page_url'));
    }

    public function test_non_admin_cannot_access_user_lookup(): void
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('admin.users.lookup'))
            ->assertNotFound();
    }
}
