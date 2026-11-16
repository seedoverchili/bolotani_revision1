<?php

use App\Http\Controllers\Penjual\AuthController;
use App\Http\Controllers\Penjual\DashboardController;
use App\Http\Controllers\Penjual\PhotoController;
use App\Http\Controllers\Penjual\ProductController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// ---------- Auth pedagang ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');

    // SEMENTARA (Tahap 2): diganti PasswordOtpController di Tahap 5.
    Route::get('/lupa-sandi', fn () => view('auth.forgot'))->name('password.request');
    Route::post('/lupa-sandi', fn () => abort(501, 'Belum tersedia (Tahap 5).'))->name('password.email');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Dashboard pedagang ----------
Route::middleware(['auth', EnsureRole::class.':penjual'])->prefix('penjual')->name('penjual.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/profil', [DashboardController::class, 'updateProfile'])->name('profil.update');
    Route::get('/foto', [PhotoController::class, 'edit'])->name('foto');
    Route::put('/foto', [PhotoController::class, 'update'])->name('foto.update');

    Route::get('/produk/tambah', [ProductController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProductController::class, 'store'])->name('produk.store');
    Route::patch('/produk/{product}/stok', [ProductController::class, 'toggleStock'])->name('produk.stok');
    Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('produk.destroy');
});