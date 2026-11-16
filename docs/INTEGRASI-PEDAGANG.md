# Catatan Integrasi: Modul Pedagang Bolo Tani

Dokumen ini untuk penggabungan modul pedagang dengan modul pembeli (dan admin) ke satu aplikasi dan satu database.

## 1. Rute modul pedagang (`routes/penjual.php`)

Dipanggil dari `routes/web.php` lewat `require __DIR__.'/penjual.php';`.

| Method | URI | Nama route | Controller@aksi | Middleware |
|---|---|---|---|---|
| GET | /masuk | `login` | Penjual\AuthController@showLogin | guest |
| POST | /masuk | `login.store` | Penjual\AuthController@login | guest, throttle:login |
| GET | /daftar | `register` | Penjual\AuthController@showRegister | guest |
| POST | /daftar | `register.store` | Penjual\AuthController@register | guest, throttle:register |
| GET | /lupa-sandi | `password.request` | Penjual\PasswordOtpController@request | guest |
| POST | /lupa-sandi | `password.email` | Penjual\PasswordOtpController@send | guest, throttle:otp-send |
| GET | /lupa-sandi/kode | `password.otp` | Penjual\PasswordOtpController@form | guest |
| POST | /lupa-sandi/kode | `password.otp.verify` | Penjual\PasswordOtpController@verify | guest, throttle:otp-verify |
| POST | /keluar | `logout` | Penjual\AuthController@logout | auth |
| GET | /penjual | `penjual.dashboard` | Penjual\DashboardController@index | auth, EnsureRole:penjual |
| PUT | /penjual/profil | `penjual.profil.update` | Penjual\DashboardController@updateProfile | auth, EnsureRole:penjual |
| GET | /penjual/foto | `penjual.foto` | Penjual\PhotoController@edit | auth, EnsureRole:penjual |
| PUT | /penjual/foto | `penjual.foto.update` | Penjual\PhotoController@update | auth, EnsureRole:penjual |
| GET | /penjual/produk/tambah | `penjual.produk.create` | Penjual\ProductController@create | auth, EnsureRole:penjual |
| POST | /penjual/produk | `penjual.produk.store` | Penjual\ProductController@store | auth, EnsureRole:penjual |
| PATCH | /penjual/produk/{product}/stok | `penjual.produk.stok` | Penjual\ProductController@toggleStock | auth, EnsureRole:penjual |
| DELETE | /penjual/produk/{product} | `penjual.produk.destroy` | Penjual\ProductController@destroy | auth, EnsureRole:penjual |

**Dua nama route ini TIDAK ada di daftar kontrak awal tetapi dipakai view:** `login.store` dan `register.store`.

## 2. Route yang harus disediakan modul pembeli

Tampilan pedagang memanggil dua route ini. Saat ini diisi placeholder di `routes/placeholder-pembeli.php`.

| Nama route | Dipakai oleh | Parameter |
|---|---|---|
| `marketplace` | tombol "Ke Marketplace", logo, layout auth/dash | tanpa parameter |
| `pedagang.show` | tombol "Lihat Toko Publik" di dashboard | satu parameter: `$tenant->slug` |

**Saat digabung:** hapus `routes/placeholder-pembeli.php` dan barisnya di `routes/web.php`.

## 3. Tabel dan kolom

Tabel milik modul ini: `users` (kolom `role`), `tenants`, `products`, `product_images`, `password_otps`. Skema identik dengan dump `bolotani.sql`.
Migration (nama berbeda dari proyek lama, semuanya memakai pengaman `hasTable`/`hasColumn`):

- `2026_11_14_100001_add_role_to_users_table.php`
- `2026_11_14_100002_create_tenants_table.php`
- `2026_11_14_100003_create_products_table.php` (kolom `status` sudah digabung di sini)
- `2026_11_14_100004_create_product_images_table.php`
- `2026_11_14_100005_create_password_otps_table.php`

Aturan data yang dipegang modul pedagang:

- `users.role`: pedagang menulis `'penjual'`. Default kolom `'penjual'` (sesuai dump), jadi **modul pembeli wajib mengisi `role='pembeli'` secara eksplisit**.
- `tenants.status`: `'approved'` saat registrasi. `tenants.code` berbentuk `TKT-TAHUN-ID4DIGIT`.
- `products.status`: `'pending'` saat dibuat pedagang; admin mengubahnya menjadi `'approved'`. `stock` hanya bernilai 0 atau 100.
- `products.sku` dibuat dari ID produk (`XXX-KAT-0001`), selalu unik.
- `products.description` sudah dibersihkan, hanya tag `b strong i em ul ol li br p div` tanpa atribut. Aman dicetak dengan `{!! $product->descriptionHtml() !!}`.
- `password_otps.code_hash` berisi HMAC-SHA256 (bukan bcrypt), jadi tidak bisa diverifikasi dengan `Hash::check`.

## 4. Model dan relasi

`App\Models\User`, `Tenant`, `Product`, `ProductImage`.

- `User` 1:1 `Tenant` (`user_id`) · `Tenant` 1:N `Product` (`tenant_id`) · `Product` 1:N `ProductImage` (`product_id`, urut `sort`).
- `Product::scopeApproved()`, `Product::CATEGORIES`, `Product::cleanHtml()` (statis), `Product::coverUrl()`, `descriptionHtml()`.
- Method pembantu `Tenant`: `avatarUrl()`, `coverUrl()`, `waLink()`, `areaLabel()`, `mapsLink()`, `initials()`.
- Kolom `tenants.wa_clicks` **ditulis oleh modul pembeli** (redirect WhatsApp), dibaca oleh dashboard pedagang.

## 5. Foto

Disk `public`, path tersimpan relatif ke `public/`: `storage/products/<file>` dan `storage/tenants/<file>`. Jalankan `php artisan storage:link`. Gambar bawaan: `public/images/products/kaktus-mini.jpg` dan `public/images/tenants/rohmad-cover.jpg`.

## 6. File di luar folder pedagang (periksa bentrok saat merge)

Folder kontrak: `app/Http/Controllers/Penjual/`, `resources/views/penjual/`, `resources/views/auth/`, `routes/penjual.php`. Tambahan yang tidak bisa ditaruh di sana:

| File | Catatan |
|---|---|
| `app/Http/Middleware/EnsureRole.php` | dipakai sebagai `EnsureRole::class.':penjual'`. Bila admin punya kelas bernama sama, **satukan** |
| `app/Providers/PenjualServiceProvider.php` | limiter `login`, `register`, `otp-send`, `otp-verify`. Terdaftar di `bootstrap/providers.php`. Bila `AppServiceProvider` sudah mendefinisikan limiter `login`, pilih satu |
| `app/Services/Penjual/OtpService.php` | logika OTP |
| `app/Mail/Penjual/OtpMail.php` + `resources/views/auth/mail-otp.blade.php` | email OTP |
| `app/Console/Commands/SetujuiProduk.php` | `php artisan produk:setujui` (sementara, diganti dashboard admin) |
| `resources/views/components/icon.blade.php`, `components/layouts/auth.blade.php`, `dash.blade.php`, `components/mail/layout.blade.php` | disalin apa adanya dari proyek lama; `layouts/dash` punya cabang admin yang memanggil `route('admin.dashboard')` |
| `public/css/*`, `public/js/*`, `public/images/{brand,icons,products/kaktus-mini.jpg,tenants/rohmad-cover.jpg}` | aset tampilan. `app.css` juga dipakai halaman publik |
| `database/seeders/PenjualSeeder.php`, `DatabaseSeeder.php` | akun uji; seeder dilewati di production. Gabungkan dengan memanggil `PenjualSeeder` dari `DatabaseSeeder` milik aplikasi gabungan |
| `tests/Feature/Penjual/*`, `tests/Concerns/MembuatPenjual.php` | test fitur |

File bersama yang **diubah** (cukup digabung manual): `routes/web.php` (1 baris `require` penjual + 1 baris placeholder) dan `bootstrap/providers.php` (1 baris provider). `bootstrap/app.php` dan `AppServiceProvider` tidak diubah.

## 7. Kunci session yang dipakai alur OTP

`otp_email` dan `otp_sent_at`. Jangan dipakai ulang oleh modul lain.

## 8. Konfigurasi yang dibutuhkan

- `.env`: `MAIL_MAILER=log` untuk uji (kode OTP terbaca di `storage/logs/laravel.log`, cari "Kode verifikasi Anda"); ganti dengan SMTP asli untuk produksi.
- `php artisan storage:link`.
- Batas unggah PHP: `upload_max_filesize` dan `post_max_size` minimal 6 MB (satu produk bisa 5 foto x 5 MB, jadi idealnya 30 MB).
- Ekstensi PHP: `gd` dan `fileinfo` (validasi gambar), `pdo_sqlite` (test).

## 9. Akun uji (hanya environment lokal)

`admin@bolotani.test` / `Admin12345` (role admin, tidak bisa masuk lewat `/masuk`) dan `penjual@bolotani.test` / `Penjual12345`.

## 10. Hal yang sengaja belum ada

Marketplace publik, detail produk publik, profil toko publik, redirect WhatsApp, dan dashboard admin (bagian modul lain). Pengganti sementara persetujuan produk: `php artisan produk:setujui`.
