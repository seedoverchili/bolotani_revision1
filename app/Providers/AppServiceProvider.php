<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        // Batas percobaan dipisah per fitur agar satu fitur tidak menghabiskan jatah fitur lain.
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)->by('login|'.strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(10)->by('register|'.$r->ip()));
        RateLimiter::for('otp-send', fn (Request $r) => Limit::perMinute(6)->by('otp-send|'.$r->ip()));
        RateLimiter::for('otp-verify', fn (Request $r) => Limit::perMinute(10)->by('otp-verify|'.$r->ip()));
        RateLimiter::for('admin-reset', fn (Request $r) => Limit::perMinute(6)->by('admin-reset|'.$r->ip()));
        // 7.4 — Bagian 7 · Pemulihan sandi — Batasi frekuensi permintaan. Fungsi: Membatasi permintaan pemulihan admin menjadi enam per menit per alamat IP.
    }
}