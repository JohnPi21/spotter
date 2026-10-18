<?php

namespace Tests\Feature;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\TestCase;
use Mockery;
use PDOException;

class HealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array', 'telescope.enabled' => false]);
        $this->freezeTime();
    }

    public function test_health_checks_the_database_and_returns_ok_for_guests(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->once()->with('SELECT 1')->andReturn([(object) ['1' => 1]]);
        $this->mock(DatabaseManager::class)
            ->shouldReceive('connection')->once()->withNoArgs()->andReturn($connection);

        $response = $this->get('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checked_at', now()->toISOString())
            ->assertJsonStructure(['status', 'checked_at', 'database_duration_ms']);
        $this->assertIsNumeric($response->json('database_duration_ms'));
        $this->assertGreaterThanOrEqual(0, $response->json('database_duration_ms'));
    }

    public function test_health_returns_json_service_unavailable_when_database_query_fails(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->once()->with('SELECT 1')
            ->andThrow(new QueryException('mysql', 'SELECT 1', [], new PDOException('Private database credentials')));
        $this->mock(DatabaseManager::class)
            ->shouldReceive('connection')->once()->withNoArgs()->andReturn($connection);

        $response = $this->get('/health');

        $response->assertServiceUnavailable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('checked_at', now()->toISOString())
            ->assertJsonStructure(['status', 'checked_at', 'database_duration_ms']);
        $this->assertStringNotContainsString('Private database credentials', $response->getContent());
    }

    public function test_health_returns_service_unavailable_when_connection_cannot_be_created(): void
    {
        $this->mock(DatabaseManager::class)
            ->shouldReceive('connection')->once()->withNoArgs()->andThrow(new PDOException('Connection refused'));

        $this->get('/health')->assertServiceUnavailable()->assertJsonPath('status', 'error');
    }
}
