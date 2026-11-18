<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;
// 2.2 — Bagian 2 · Masuk admin — Hubungkan rute dengan controller. Fungsi: Mengimpor dua controller admin, pemeriksa peran dari fondasi, dan pembuat rute Laravel.

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
        // 2.4 — Bagian 2 · Masuk admin — Sediakan rute formulir dan pengiriman. Fungsi: GET menampilkan formulir masuk. POST memeriksa kredensial dengan batas percobaan dari fondasi.

        Route::get('/lupa-sandi', [AuthController::class, 'forgot'])->name('password.request');
        Route::post('/lupa-sandi', [AuthController::class, 'sendLink'])->middleware('throttle:admin-reset')->name('password.email');
        // 7.1 — Bagian 7 · Pemulihan sandi — Daftarkan rute lupa sandi. Fungsi: Menerima permintaan tautan pemulihan melalui email administrator.

        Route::get('/atur-ulang-sandi/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
        Route::post('/atur-ulang-sandi', [AuthController::class, 'reset'])->middleware('throttle:admin-reset')->name('password.update');
        Route::view('/sandi-berhasil', 'admin.reset-done')->name('password.done');
        // 7.15 — Bagian 7 · Pemulihan sandi — Siapkan rute penggantian sandi. Fungsi: Membuka tautan dari email, memproses sandi baru, lalu menyediakan halaman konfirmasi.
    });

    Route::middleware(['auth', EnsureRole::class.':admin'])->scopeBindings()->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenant/{tenant}', [DashboardController::class, 'show'])->name('tenant.show');
        // 1.7 — Bagian 1 · Persetujuan produk — Buka dashboard admin. Fungsi: Admin yang telah masuk membuka dashboard. Rute detail di bawahnya menangani lapak yang nanti dipilih.

        Route::put('/tenant/{tenant}', [DashboardController::class, 'update'])->name('tenant.update');
        // 4.5 — Bagian 4 · Edit lapak — Arahkan permintaan ke controller. Fungsi: Permintaan PUT untuk satu lapak diteruskan ke update() agar data profil diperiksa.
        Route::delete('/tenant/{tenant}', [DashboardController::class, 'destroy'])->name('tenant.destroy');
        // 6.2 — Bagian 6 · Hapus lapak — Terima permintaan hapus. Fungsi: Permintaan DELETE untuk lapak yang dipilih diteruskan ke destroy().

        Route::patch('/tenant/{tenant}/produk/{product}/setujui', [DashboardController::class, 'approveProduct'])->name('tenant.product.approve');
        // 1.13 — Bagian 1 · Persetujuan produk — Cocokkan produk dengan lapak. Fungsi: Rute PATCH mencari produk di lapak yang dipilih melalui scopeBindings, lalu menjalankan approveProduct().
        Route::delete('/tenant/{tenant}/produk/{product}', [DashboardController::class, 'destroyProduct'])->name('tenant.product.destroy');
        // 5.2 — Bagian 5 · Hapus produk — Cocokkan produk dengan lapak. Fungsi: Rute DELETE memastikan produk milik lapak pada URL sebelum memanggil destroyProduct().
    });
});
