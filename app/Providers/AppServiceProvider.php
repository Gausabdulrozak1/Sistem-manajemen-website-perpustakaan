<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', static fn (User $user): bool => $user->isAdmin());

        RateLimiter::for('login', function (Request $request): Limit {
            $email = $request->input('email');
            $email = is_string($email) ? mb_strtolower($email) : '';

            return Limit::perMinute(5)->by(hash('sha256', $email.'|'.$request->ip()));
        });
    }
}
