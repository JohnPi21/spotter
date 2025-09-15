<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MesocycleUpdateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // test_it_can_update_mesocycle
    // test_it_can_activate_mesocycle
    // test_it_cannot_activate_finished_mesocycles
    //
}
