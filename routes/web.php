<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordOtpController;
use App\Http\Controllers\Penjual\DashboardController as PenjualDashboardController;
use App\Http\Controllers\Penjual\PhotoController;
use App\Http\Controllers\Penjual\ProductController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Middleware\EnsureRole;
use App\Livewire\Marketplace;
use App\Livewire\ProductDetail;
use App\Livewire\TenantProfile;
use Illuminate\Support\Facades\Route;

// ---------- Marketplace publik (Livewire) ----------
Route::get('/', Marketplace::class)->name('marketplace');
Route::get('/produk/{product:slug}', ProductDetail::class)->name('produk.show');
Route::get('/pedagang/{tenant:slug}', TenantProfile::class)->name('pedagang.show');

Route::get('/wa/produk/{product}', [WhatsAppController::class, 'product'])->name('wa.redirect');
Route::get('/wa/pedagang/{tenant}', [WhatsAppController::class, 'tenant'])->name('wa.tenant');

// ---------- Auth dan dashboard penjual ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');

    Route::get('/lupa-sandi', [PasswordOtpController::class, 'request'])->name('password.request');
    Route::post('/lupa-sandi', [PasswordOtpController::class, 'send'])->middleware('throttle:otp-send')->name('password.email');
    Route::get('/lupa-sandi/kode', [PasswordOtpController::class, 'form'])->name('password.otp');
    Route::post('/lupa-sandi/kode', [PasswordOtpController::class, 'verify'])->middleware('throttle:otp-verify')->name('password.otp.verify');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
// 8.2 — Bagian 8 · Keluar admin — Terima permintaan logout. Fungsi: Rute POST keluar memanggil logout() untuk pengguna yang memiliki sesi aktif.

// ---------- Dashboard penjual ----------
Route::middleware(['auth', EnsureRole::class.':penjual'])->prefix('penjual')->name('penjual.')->group(function () {
    Route::get('/', [PenjualDashboardController::class, 'index'])->name('dashboard');
    Route::put('/profil', [PenjualDashboardController::class, 'updateProfile'])->name('profil.update');
    Route::get('/foto', [PhotoController::class, 'edit'])->name('foto');
    Route::put('/foto', [PhotoController::class, 'update'])->name('foto.update');

    Route::get('/produk/tambah', [ProductController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProductController::class, 'store'])->name('produk.store');
    // 1.2 — Bagian 1 · Persetujuan produk — Terima pengajuan pedagang. Fungsi: POST /penjual/produk meneruskan formulir pedagang ke ProductController::store().
    Route::patch('/produk/{product}/stok', [ProductController::class, 'toggleStock'])->name('produk.stok');
    Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('produk.destroy');
});

require __DIR__.'/admin.php';
// 2.1 — Bagian 2 · Masuk admin — Daftarkan rute admin. Fungsi: Memuat rute admin dalam grup web Laravel agar formulir memakai sesi dan perlindungan CSRF.
