<?php

namespace App\Providers;

use App\Services\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Money::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (['registration' => [5, 60], 'login' => [10, 1], 'password' => [5, 1], 'checkout-quote' => [30, 1], 'checkout-attempt' => [30, 1], 'order-email' => [3, 1]] as $name => [$max,$minutes]) {
            RateLimiter::for($name, fn ($request) => Limit::perMinutes($minutes, $max)->by($name.':'.$request->ip().($name === 'order-email' ? ':'.$request->route('id') : '')));
        }

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
