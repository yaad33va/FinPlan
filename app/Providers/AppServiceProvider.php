<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use App\Services\JwtService;
use App\Services\RefreshTokenService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // The "jwt" guard driver used by the "api" guard (config/auth.php).
        Auth::extend('jwt', function (Application $app, string $name, array $config): JwtGuard {
            $guard = new JwtGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(JwtService::class),
                $app->make(RefreshTokenService::class),
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        // Brute-force protection for register/login/2FA: 10 attempts per minute per email + IP.
        RateLimiter::for('auth', function (Request $request): Limit {
            $email = $request->input('email');

            return Limit::perMinute(10)->by((is_string($email) ? strtolower($email) : '').'|'.$request->ip());
        });
    }
}
