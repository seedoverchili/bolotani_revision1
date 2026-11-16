<?php

// SEMENTARA: pengganti route milik modul pembeli.
// HAPUS file ini (dan barisnya di web.php) saat dua modul digabung.

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response(
    'Marketplace (placeholder modul pembeli). <a href="'.route('login').'">Masuk pedagang</a>'
))->name('marketplace');

Route::get('/pedagang/{tenant:slug}', fn (Tenant $tenant) => response(
    'Profil toko (placeholder modul pembeli): '.e($tenant->name)
))->name('pedagang.show');