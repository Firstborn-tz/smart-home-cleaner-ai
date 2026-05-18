<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Gates
        Gate::define('access-admin', function (User $user) {
            return in_array($user->user_type, ['admin', 'super_admin']);
        });

        Gate::define('access-cleaner', function (User $user) {
            return $user->user_type === 'cleaner';
        });

        Gate::define('access-homeowner', function (User $user) {
            return $user->user_type === 'homeowner';
        });

        // View composers
        \Illuminate\Support\Facades\View::composer('layouts.cleaner', function ($view) {
            $view->with('cleaner', auth()->user()?->cleaner);
        });

        \Illuminate\Support\Facades\View::composer('layouts.homeowner', function ($view) {
            $view->with('homeowner', auth()->user()?->homeowner);
        });
    }
}