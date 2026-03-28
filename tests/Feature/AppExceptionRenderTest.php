<?php

namespace Tests\Feature;

use App\Exceptions\AppException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AppExceptionRenderTest extends TestCase
{
    public function test_non_get_app_exception_redirects_back_with_error_message(): void
    {
        Route::middleware('web')->post('/test-app-exception', function () {
            throw new AppException(503, 'AI provider is not available', 'AI_PROVIDER_UNAVAILABLE');
        });

        $this->from('/mesocycles/ai/create')
            ->post('/test-app-exception')
            ->assertRedirect('/mesocycles/ai/create')
            ->assertSessionHas('error', 'AI provider is not available');
    }
}
