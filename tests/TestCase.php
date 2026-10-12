<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function signIn(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $this->actingAs($user);

        return $user;
    }
}
