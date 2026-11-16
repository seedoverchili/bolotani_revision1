<?php

use App\Http\Controllers\Penjual\AuthController;
use App\Http\Controllers\Penjual\DashboardController;
use App\Http\Controllers\Penjual\PasswordOtpController;
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

    // Lupa sandi via OTP email
    Route::get('/lupa-sandi', [PasswordOtpController::class, 'request'])->name('password.request');
    Route::post('/lupa-sandi', [PasswordOtpController::class, 'send'])->middleware('throttle:otp-send')->name('password.email');
    Route::get('/lupa-sandi/kode', [PasswordOtpController::class, 'form'])->name('password.otp');
    Route::post('/lupa-sandi/kode', [PasswordOtpController::class, 'verify'])->middleware('throttle:otp-verify')->name('password.otp.verify');
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
