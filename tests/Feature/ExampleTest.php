<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_homepage_redirects_to_the_admin_login(): void
    {
        $this->get('/')->assertRedirectToRoute('login');
    }
}
