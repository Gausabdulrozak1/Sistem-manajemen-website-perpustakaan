<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManageUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(12);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(ManageUserRequest $request): RedirectResponse
    {
        User::query()->create($request->validated());

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(ManageUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $user): void {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->isAdmin() && $data['role'] !== 'admin' && $adminIds->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Perpustakaan harus memiliki setidaknya satu admin.']);
            }

            if ($request->user()->is($lockedUser) && $data['role'] !== 'admin') {
                throw ValidationException::withMessages(['role' => 'Anda tidak dapat menurunkan peran akun yang sedang digunakan.']);
            }

            if (blank($data['password'])) {
                unset($data['password']);
            }

            $lockedUser->update($data);
        });

        return redirect()->route('users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }

        DB::transaction(function () use ($user): void {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->isAdmin() && $adminIds->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'Admin terakhir tidak dapat dihapus.']);
            }

            $lockedUser->delete();
        });

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
