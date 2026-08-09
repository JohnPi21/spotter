<?php

namespace Tests\Feature\Actions\User;

use App\Actions\User\ListUsers;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    public function test_it_filters_users_by_id(): void
    {
        $matchingUser = User::factory()->create();
        User::factory()->create();

        $users = (new ListUsers)->execute([
            'filter' => ['id' => (string) $matchingUser->id],
        ]);

        $this->assertSame([$matchingUser->id], $users->pluck('id')->all());
    }

    public function test_it_filters_users_by_partial_name(): void
    {
        $matchingUser = User::factory()->create(['name' => 'Jonathan']);
        User::factory()->create(['name' => 'Alice']);

        $users = (new ListUsers)->execute([
            'filter' => ['name' => 'nath'],
        ]);

        $this->assertSame([$matchingUser->id], $users->pluck('id')->all());
    }

    public function test_it_filters_users_by_partial_email(): void
    {
        $matchingUser = User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'alice@example.com']);

        $users = (new ListUsers)->execute([
            'filter' => ['email' => 'john@'],
        ]);

        $this->assertSame([$matchingUser->id], $users->pluck('id')->all());
    }

    /**
     * @return array<string, array{bool|string, bool}>
     */
    public static function verifiedFilterProvider(): array
    {
        return [
            'verified boolean' => [true, true],
            'unverified boolean' => [false, false],
            'verified query string' => ['1', true],
            'unverified query string' => ['0', false],
        ];
    }

    #[DataProvider('verifiedFilterProvider')]
    public function test_it_filters_users_by_verification_status(bool|string $filter, bool $isVerified): void
    {
        $verifiedUser = User::factory()->create();
        $unverifiedUser = User::factory()->unverified()->create();

        $users = (new ListUsers)->execute([
            'filter' => ['verified' => $filter],
        ]);

        $expectedUser = $isVerified ? $verifiedUser : $unverifiedUser;

        $this->assertSame([$expectedUser->id], $users->pluck('id')->all());
    }

    public function test_it_filters_users_by_created_at_date(): void
    {
        $matchingUser = User::factory()->create(['created_at' => '2026-07-19 15:30:00']);
        User::factory()->create(['created_at' => '2026-07-18 15:30:00']);

        $users = (new ListUsers)->execute([
            'filter' => ['created_at' => '2026-07-19'],
        ]);

        $this->assertSame([$matchingUser->id], $users->pluck('id')->all());
    }

    public function test_it_uses_descending_sort_direction_by_default(): void
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Bob']);

        $users = (new ListUsers)->execute(['sort' => 'name']);

        $this->assertSame(['Charlie', 'Bob', 'Alice'], $users->pluck('name')->all());
    }

    public function test_it_uses_the_requested_sort_direction(): void
    {
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $users = (new ListUsers)->execute([
            'sort' => 'name',
            'direction' => 'asc',
        ]);

        $this->assertSame(['Alice', 'Bob', 'Charlie'], $users->pluck('name')->all());
    }

    public function test_verified_sort_maps_to_email_verified_at(): void
    {
        $unverifiedUser = User::factory()->unverified()->create();
        $verifiedUser = User::factory()->create([
            'email_verified_at' => '2026-07-19 15:30:00',
        ]);

        $users = (new ListUsers)->execute([
            'sort' => 'verified',
            'direction' => 'desc',
        ]);

        $this->assertSame(
            [$verifiedUser->id, $unverifiedUser->id],
            $users->pluck('id')->all(),
        );
    }
}
