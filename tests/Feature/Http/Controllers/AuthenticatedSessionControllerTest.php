<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_displays_the_admin_sign_in_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke akun admin')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_guest_is_redirected_to_login_before_opening_admin_pages(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_admin_can_log_in_and_return_to_the_page_they_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@ruangbaca.test',
            'password' => 'kata-sandi-aman',
        ]);

        $this->get(route('books.index'));
        $response = $this->post(route('login.store'), [
            'email' => 'ADMIN@RUANGBACA.TEST',
            'password' => 'kata-sandi-aman',
        ]);

        $response->assertRedirectToRoute('books.index');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_start_an_admin_session(): void
    {
        User::factory()->create([
            'email' => 'admin@ruangbaca.test',
            'password' => 'kata-sandi-aman',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@ruangbaca.test',
            'password' => 'kata-sandi-salah',
        ]);

        $response->assertRedirectToRoute('login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => 'unknown@ruangbaca.test',
                'password' => 'kata-sandi-salah',
            ])->assertRedirectToRoute('login');
        }

        $this->post(route('login.store'), [
            'email' => 'unknown@ruangbaca.test',
            'password' => 'kata-sandi-salah',
        ])->assertTooManyRequests();
    }

    public function test_admin_can_log_out_and_must_sign_in_again(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('logout'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
    }

    public function test_initial_admin_can_be_created_through_the_interactive_command(): void
    {
        $this->artisan('library:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Perpustakaan')
            ->expectsQuestion('Email admin', 'admin@ruangbaca.test')
            ->expectsQuestion('Kata sandi (minimal 12 karakter)', 'rahasia-yang-panjang')
            ->expectsQuestion('Ulangi kata sandi', 'rahasia-yang-panjang')
            ->expectsOutputToContain('berhasil dibuat')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Perpustakaan',
            'email' => 'admin@ruangbaca.test',
            'role' => 'admin',
        ]);
        $this->assertDatabaseMissing('users', ['password' => 'rahasia-yang-panjang']);
    }

    public function test_initial_admin_command_will_not_add_accounts_after_setup(): void
    {
        User::factory()->create();

        $this->artisan('library:create-admin')
            ->expectsOutputToContain('Akun pengguna sudah ada')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_password_can_be_reset_from_the_local_console_without_changing_its_role(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@ruangbaca.test',
            'role' => 'admin',
            'password' => 'kata-sandi-lama',
        ]);

        $this->artisan('library:reset-password', ['email' => $user->email])
            ->expectsQuestion('Kata sandi baru (minimal 12 karakter)', 'kata-sandi-baru-aman')
            ->expectsQuestion('Ulangi kata sandi baru', 'kata-sandi-baru-aman')
            ->expectsOutputToContain('berhasil diperbarui')
            ->assertExitCode(0);

        $user->refresh();
        $this->assertSame('admin', $user->role);
        $this->assertTrue(Hash::check('kata-sandi-baru-aman', $user->password));
    }
}
