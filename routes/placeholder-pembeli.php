<?php

// SEMENTARA: pengganti route milik modul pembeli.
// HAPUS file ini (dan barisnya di web.php) saat dua modul digabung.

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $html = 'Marketplace (placeholder modul pembeli). ';

    if (Auth::check()) {
        $html .= 'Anda login sebagai '.e(Auth::user()->email).'. '
            .'<a href="'.route('penjual.dashboard').'">Ke dashboard</a> '
            .'<form method="POST" action="'.route('logout').'" style="display:inline">'
            .csrf_field().'<button>Keluar</button></form>';
    } else {
        $html .= '<a href="'.route('login').'">Masuk pedagang</a>';
    }

    return response($html);
})->name('marketplace');

Route::get('/pedagang/{tenant:slug}', fn (Tenant $tenant) => response(
    'Profil toko (placeholder modul pembeli): '.e($tenant->name)
))->name('pedagang.show');