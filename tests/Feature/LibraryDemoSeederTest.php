<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LibraryDemoSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_database_seeder_creates_repeatable_demo_library_data_without_a_default_login(): void
    {
        $this->seed();

        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('books', 6);
        $this->assertDatabaseCount('members', 5);
        $this->assertDatabaseCount('users', 0);

        $this->seed();

        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('books', 6);
        $this->assertDatabaseCount('members', 5);
        $this->assertDatabaseCount('users', 0);
    }
}
