<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/** Rate limiter modul pedagang. */
class PenjualServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)
            ->by('login|'.strtolower((string) $r->input('email')).'|'.$r->ip()));

        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(10)
            ->by('register|'.$r->ip()));

        // Kirim kode: dibatasi per IP, dan per email agar satu alamat tidak dibanjiri email.
        RateLimiter::for('otp-send', fn (Request $r) => [
            Limit::perMinute(6)->by('otp-send|'.$r->ip()),
            Limit::perMinutes(10, 5)->by('otp-send-email|'.strtolower(trim((string) $r->input('email')))),
        ]);

        // Verifikasi kode: dibatasi per IP (batas 5 salah per kode ada di OtpService).
        RateLimiter::for('otp-verify', fn (Request $r) => Limit::perMinute(10)
            ->by('otp-verify|'.$r->ip()));
    }
}
