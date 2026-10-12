<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('library:create-admin')]
#[Description('Buat akun admin perpustakaan pertama.')]
class CreateLibraryAdmin extends Command
{
    public function handle(): int
    {
        if (User::query()->exists()) {
            $this->components->error('Akun pengguna sudah ada. Tidak ada akun baru yang dibuat.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama admin'));
        $email = mb_strtolower(trim((string) $this->ask('Email admin')));
        $password = $this->secret('Kata sandi (minimal 12 karakter)');
        $passwordConfirmation = $this->secret('Ulangi kata sandi');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
        ]);

        $this->components->info("Akun admin {$email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
