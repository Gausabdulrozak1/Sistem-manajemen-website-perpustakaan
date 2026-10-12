<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('library:reset-password {email?}')]
#[Description('Atur ulang kata sandi akun perpustakaan secara aman.')]
class ResetLibraryPassword extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');
        $email = mb_strtolower(trim(is_string($email) ? $email : (string) $this->ask('Email akun')));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->components->error('Akun dengan email tersebut tidak ditemukan.');

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi baru (minimal 12 karakter)');
        $passwordConfirmation = $this->secret('Ulangi kata sandi baru');
        $validator = Validator::make([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user->update(['password' => $password]);
        $this->components->info("Kata sandi akun {$email} berhasil diperbarui.");

        return self::SUCCESS;
    }
}
