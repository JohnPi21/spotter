<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MesoDayUpdateTest extends TestCase
{
    public function test_user_can_toggle_day(): void {}

    public function test_user_cannot_finish_day_with_unfinished_sets(): void {}

    public function test_user_cannot_toggle_other_users_days(): void {}
}
