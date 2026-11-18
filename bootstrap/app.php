<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));
        // 2.3 — Bagian 2 · Masuk admin — Arahkan tamu ke halaman masuk. Fungsi: Mengarahkan tamu pada halaman masuk sesuai alamat yang sedang dibuka.

        $middleware->redirectUsersTo(fn (Request $request) => $request->user()->isAdmin()
            ? route('admin.dashboard')
            : route('penjual.dashboard'));
        // 2.14 — Bagian 2 · Masuk admin — Arahkan pengguna yang sudah masuk. Fungsi: Membawa pengguna yang sudah masuk ke dasbor perannya ketika membuka formulir khusus tamu.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
