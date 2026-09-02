<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Super admins bypass every permission check.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(Rbac::ROLE_SUPER_ADMIN) ? true : null;
        });

        // API rate limit — per token, falling back to per IP.
        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->currentAccessToken()?->id
                ?? $request->user()?->id
                ?? $request->ip();

            return Limit::perMinute(120)->by((string) $key);
        });
    }
}
