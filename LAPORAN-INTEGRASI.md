# Laporan Integrasi Bolo Tani — Pembeli + Pedagang + Admin

Tanggal: 18 November 2026
Fondasi: `bolotani_revision1-main.zip`
Sumber modul pembeli: `laravel-pembeli.zip`
Prinsip kerja: **add and integrate, not replace**.

## 1. Ringkasan

Modul marketplace publik (Livewire) dari ZIP pembeli sudah dimasukkan ke proyek utama.
Seluruh kode pedagang, admin, autentikasi, OTP, email, middleware, model, migration,
seeder, CSS, dan aset dari proyek utama tetap dipertahankan. Tidak ada file proyek
utama yang dihapus dan tidak ada file yang ditimpa dengan versi ZIP pembeli.

Struktur akhir mengikuti panduan PDF: pembeli memakai Livewire + Blade, pedagang dan
admin memakai Controller + Blade, dengan satu set Model Eloquent dan satu database.

## 2. File baru

| File | Keterangan |
|---|---|
| `app/Livewire/Marketplace.php` | Komponen halaman utama marketplace, pencarian, filter kategori, pagination. |
| `app/Livewire/ProductDetail.php` | Detail produk, galeri gambar, produk terkait, proteksi status persetujuan. |
| `app/Livewire/TenantProfile.php` | Profil publik pedagang, pencarian dan filter kelompok produk. |
| `app/Http/Controllers/WhatsAppController.php` | Redirect WhatsApp untuk produk dan pedagang, menambah `wa_clicks`. |
| `resources/views/livewire/marketplace.blade.php` | Tampilan marketplace. |
| `resources/views/livewire/product-detail.blade.php` | Tampilan detail produk. |
| `resources/views/livewire/tenant-profile.blade.php` | Tampilan profil pedagang. |
| `LAPORAN-INTEGRASI.md` | Laporan ini. |

## 3. File yang digabungkan / disesuaikan

| File | Perubahan |
|---|---|
| `routes/web.php` | Dua route placeholder (`/` dan `/pedagang/{tenant:slug}`) diganti dengan komponen Livewire. Ditambahkan `/produk/{product:slug}`, `/wa/produk/{product}`, dan `/wa/pedagang/{tenant}`. Seluruh route auth, OTP, reset password, dashboard penjual, dan `require __DIR__.'/admin.php';` tetap utuh. |
| `composer.json` | Menambahkan `livewire/livewire: ^4.4` (terpasang v4.4.8). Versi Laravel dan dependensi lain tidak diubah. |
| `composer.lock` | Diperbarui hanya untuk memasukkan Livewire beserta dependensinya. |
| `tests/Feature/ExampleTest.php` | Menambahkan `RefreshDatabase`. Halaman `/` sekarang melakukan query database, sehingga smoke test lama perlu skema database. Assertion `assertStatus(200)` tidak diubah. |

## 4. File fondasi yang tidak diubah

- `app/Providers/AppServiceProvider.php` — seluruh RateLimiter login, register, OTP, dan reset admin dipertahankan.
- `bootstrap/app.php` — redirect tamu/admin dan redirect berdasarkan role dipertahankan.
- `app/Models/User.php`, `Tenant.php`, `Product.php`, `ProductImage.php` — relasi dan method yang dipakai pembeli sudah tersedia; tidak ada perubahan.
- `routes/admin.php` — seluruh route admin dipertahankan.
- `database/migrations/*` dan `database/seeders/DatabaseSeeder.php` — tidak diubah dan tidak ada migration baru.
- Controller `Admin/*`, `AuthController`, `PasswordOtpController`, `Penjual/*` — tidak diubah.
- `resources/views/layouts/public.blade.php`, `partials/site-header.blade.php`, `partials/site-footer.blade.php` — sudah tersedia di proyek utama dan dipakai ulang tanpa perubahan.
- `public/css/app.css`, `public/images/icons/*`, `public/images/products/*`, `public/images/tenants/*` — sudah lengkap; semua ikon dan gambar yang direferensikan komponen pembeli tersedia.

## 5. Kompatibilitas database

Tidak ada perubahan skema. Kolom yang dipakai komponen pembeli sudah ada pada
migration proyek utama:

- `tenants`: `status`, `approved_at`, `wa_clicks`, `slug`, `avatar`, `cover`, `cover_pos`, `lat`, `lng`.
- `products`: `status`, `category`, `categories`, `slug`, `tags`, `group`, `is_featured`, `badge`, `badge_tone`, `stock`.
- `product_images`: `path`, `sort`.

Filter persetujuan yang berlaku:

- Marketplace hanya menampilkan produk `approved` dari tenant `approved`.
- Detail produk dan profil pedagang mengembalikan 404 untuk data yang belum disetujui, kecuali pemilik atau admin.
- Daftar produk pada profil pedagang hanya menampilkan produk `approved` kepada pengunjung.

## 6. Pengujian yang dilakukan

Lingkungan: PHP 8.4.26, Composer 2.10.3, Laravel 13.17, Livewire 4.4.8, Node 23.11.1, SQLite lokal untuk pengujian.

| Pemeriksaan | Hasil |
|---|---|
| `php -l` pada seluruh file PHP baru dan `routes/web.php` | Tidak ada syntax error. |
| `composer validate` | `composer.json is valid`. |
| `php artisan optimize:clear` | Berhasil. |
| `php artisan route:list` | 35 route terdaftar; tidak ada tabrakan nama/URL. |
| `php artisan view:cache` | Seluruh Blade berhasil dikompilasi. |
| `php artisan test` | 8 test lulus, 62 assertion (termasuk 6 test admin yang sudah ada). |
| `npm install` + `npm run build` | Build Vite berhasil. |
| `GET /`, `/?cari=alpukat`, `/?kategori=produk_olahan`, `?page=2` | HTTP 200; pencarian, filter, dan pagination mengembalikan data yang sesuai. |
| `GET /produk/{slug}` dan `GET /pedagang/{slug}` | HTTP 200 dan konten produk/profil tampil. |
| `GET /wa/produk/{id}` dan `/wa/pedagang/{id}` | HTTP 302 ke `https://wa.me/...`. |
| Tenant/produk berstatus `pending` | Halaman publik mengembalikan HTTP 404 dan tidak muncul di marketplace. |
| Login pedagang (`penjual@gmail.com`) → `/penjual`, `/penjual/produk/tambah`, `/penjual/foto` | Login 302 ke dashboard; ketiga halaman HTTP 200. |
| Pedagang membuka `/admin` | HTTP 403 (role tetap dibatasi). |
| Login admin (`mohridwanramadan@gmail.com`) → `/admin`, `/admin/tenant/{id}` | HTTP 200; data pedagang tampil. |
| Tamu membuka `/admin` | Redirect ke `/admin/masuk`. |

## 7. Keterbatasan pengujian

- Pengujian HTTP dijalankan terhadap server lokal dengan database SQLite. Koneksi MySQL
  produksi tidak dijalankan pada mesin ini, tetapi tidak ada perubahan skema sehingga
  migration MySQL proyek utama tetap kompatibel.
- Interaksi JavaScript Livewire (klik kategori, debounce pencarian, salin nomor, navigasi
  `wire:navigate`) diverifikasi dari sisi server-rendered HTML dan pemuatan aset Livewire,
  bukan melalui pengujian browser otomatis.
- Pengiriman email/OTP nyata tidak diuji karena membutuhkan layanan email eksternal.
  Test admin yang ada tetap lulus dengan mailer palsu.
- `vendor/`, `node_modules/`, `.env`, database SQLite hasil pengujian, cache, dan
  `public/build` tidak disertakan dalam ZIP. Jalankan `composer install`, salin
  `.env.example` menjadi `.env`, lalu `php artisan key:generate` sebelum menjalankan aplikasi.
