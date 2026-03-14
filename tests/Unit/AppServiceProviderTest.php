<?php

namespace Tests\Unit;

use App\Contracts\AiClient;
use App\Services\PrismAiClient;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class AppServiceProviderTest extends BaseTestCase
{
    public function test_ai_client_binding_resolves_prism_ai_client(): void
    {
        $this->assertInstanceOf(PrismAiClient::class, $this->app->make(AiClient::class));
    }
}
