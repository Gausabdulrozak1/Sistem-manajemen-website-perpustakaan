<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_only_admin_can_open_user_management(): void
    {
        $this->get(route('users.index'))->assertRedirectToRoute('login');

        $this->signIn(['role' => 'staff']);
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
    }

    public function test_admin_can_create_a_staff_account_with_a_hashed_password(): void
    {
        $this->signIn(['role' => 'admin']);

        $this->post(route('users.store'), [
            'name' => '  Petugas Satu  ',
            'email' => 'PETUGAS@RUANGBACA.TEST',
            'role' => 'staff',
            'password' => 'kata-sandi-petugas',
            'password_confirmation' => 'kata-sandi-petugas',
        ])->assertRedirectToRoute('users.index')
            ->assertSessionHas('success', 'Akun pengguna berhasil dibuat.');

        $user = User::query()->where('email', 'petugas@ruangbaca.test')->firstOrFail();
        $this->assertSame('Petugas Satu', $user->name);
        $this->assertSame('staff', $user->role);
        $this->assertTrue(Hash::check('kata-sandi-petugas', $user->password));
    }

    public function test_staff_can_manage_library_data_but_cannot_change_roles(): void
    {
        $this->signIn(['role' => 'staff']);

        $this->get(route('books.index'))->assertOk();
        $this->get(route('loans.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_change_a_users_role_and_leave_password_unchanged(): void
    {
        $this->signIn(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $oldPassword = $staff->password;

        $this->put(route('users.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirectToRoute('users.index');

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'password' => $oldPassword]);
    }

    public function test_admin_cannot_remove_the_last_admin_role_or_account(): void
    {
        $admin = $this->signIn(['role' => 'admin']);

        $this->from(route('users.edit', $admin))->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staff',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('role');

        $this->from(route('users.index'))->delete(route('users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->signIn(['role' => 'admin']);
        User::factory()->create(['role' => 'admin']);

        $this->from(route('users.index'))->delete(route('users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
