<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/** Rate limiter modul pedagang (otp-send dan otp-verify ditambahkan di Tahap 5). */
class PenjualServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)
            ->by('login|'.strtolower((string) $r->input('email')).'|'.$r->ip()));

        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(10)
            ->by('register|'.$r->ip()));
    }
}