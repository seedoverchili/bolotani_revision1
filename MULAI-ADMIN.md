# Bolo Tani · Urutan belajar per fitur

Angka pertama menunjukkan bagian atau fitur. Angka setelah titik menunjukkan langkah dalam fitur tersebut. Misalnya, `// 1.4` berarti Bagian 1, langkah 4. Saat pindah ke Bagian 2, langkah dimulai lagi dari `// 2.1`. Baca kode tepat di atas setiap komentar.

| Bagian | Fitur | Urutan langkah |
| --- | --- | --- |
| 1 | Pengajuan dan persetujuan produk | 1.1–1.16 |
| 2 | Masuk dan hak akses admin | 2.1–2.18 |
| 3 | Dashboard dan pencarian lapak | 3.1–3.11 |
| 4 | Edit profil dan kontak lapak | 4.1–4.8 |
| 5 | Hapus produk | 5.1–5.4 |
| 6 | Hapus lapak | 6.1–6.4 |
| 7 | Pemulihan kata sandi | 7.1–7.30 |
| 8 | Keluar dari akun | 8.1–8.3 |
| 9 | Pengujian fitur admin | 9.1–9.8 |

## Bagian 1 · Pengajuan dan persetujuan produk

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 1.1` | **Ajukan produk dari formulir** | Pedagang menekan Simpan & Ajukan Produk dan mengonfirmasi pengiriman data serta foto. [resources/views/penjual/produk-tambah.blade.php, baris 98](resources/views/penjual/produk-tambah.blade.php#L98) |
| `// 1.2` | **Terima pengajuan pedagang** | POST /penjual/produk meneruskan formulir pedagang ke ProductController::store(). [routes/web.php, baris 38](routes/web.php#L38) |
| `// 1.3` | **Validasi data dan foto** | Memeriksa kategori, nama, harga, satuan, deskripsi, dan satu hingga lima foto sebelum menyimpan. [app/Http/Controllers/Penjual/ProductController.php, baris 59](app/Http/Controllers/Penjual/ProductController.php#L59) |
| `// 1.4` | **Simpan produk dengan status pending** | Menetapkan status awal produk ke pending saat menyimpannya untuk lapak pedagang. [app/Http/Controllers/Penjual/ProductController.php, baris 77](app/Http/Controllers/Penjual/ProductController.php#L77) |
| `// 1.5` | **Konfirmasikan pengajuan** | Setelah produk dan foto tersimpan, pedagang kembali ke formulir dengan pesan pengajuan berhasil. [app/Http/Controllers/Penjual/ProductController.php, baris 92](app/Http/Controllers/Penjual/ProductController.php#L92) |
| `// 1.6` | **Tampilkan status pada dashboard pedagang** | Saat pedagang membuka dashboard, produk yang masih pending diberi label Menunggu persetujuan admin. [resources/views/penjual/dashboard.blade.php, baris 103](resources/views/penjual/dashboard.blade.php#L103) |
| `// 1.7` | **Buka dashboard admin** | Admin yang telah masuk membuka dashboard. Rute detail di bawahnya menangani lapak yang nanti dipilih. [routes/admin.php, baris 28](routes/admin.php#L28) |
| `// 1.8` | **Urutkan lapak berdasarkan antrean** | Mencari lapak di seluruh daftar sebelum membagi halaman. Lapak dengan antrean persetujuan terbanyak tampil dahulu. [app/Http/Controllers/Admin/DashboardController.php, baris 44](app/Http/Controllers/Admin/DashboardController.php#L44) |
| `// 1.9` | **Pilih lapak yang perlu diperiksa** | Admin melihat jumlah yang menunggu pada setiap lapak dan membuka Detail & Produk untuk memeriksanya. [resources/views/admin/dashboard.blade.php, baris 96](resources/views/admin/dashboard.blade.php#L96) |
| `// 1.10` | **Muat produk dan fotonya** | Memuat profil, daftar kategori, dan foto produk dalam satu rangkaian kueri. Produk menunggu muncul di atas untuk diperiksa. [app/Http/Controllers/Admin/DashboardController.php, baris 66](app/Http/Controllers/Admin/DashboardController.php#L66) |
| `// 1.11` | **Periksa produk dan klik Setujui** | Admin memeriksa foto, harga, dan stok produk berstatus Menunggu, lalu menekan tombol Setujui pada baris produk tersebut. [resources/views/admin/tenant.blade.php, baris 147](resources/views/admin/tenant.blade.php#L147) |
| `// 1.12` | **Kirim formulir persetujuan** | Formulir produk terpilih mengirim PATCH ke rute persetujuan dengan token CSRF. [resources/views/admin/tenant.blade.php, baris 164](resources/views/admin/tenant.blade.php#L164) |
| `// 1.13` | **Cocokkan produk dengan lapak** | Rute PATCH mencari produk di lapak yang dipilih melalui scopeBindings, lalu menjalankan approveProduct(). [routes/admin.php, baris 36](routes/admin.php#L36) |
| `// 1.14` | **Simpan keputusan persetujuan** | approveProduct() mengubah pending menjadi approved pada produk yang dipilih, lalu mengarahkan admin kembali ke detail lapak. Stok tetap sama. [app/Http/Controllers/Admin/DashboardController.php, baris 107](app/Http/Controllers/Admin/DashboardController.php#L107) |
| `// 1.15` | **Tampilkan pesan hasil** | Setelah controller kembali ke detail lapak, halaman menampilkan pesan hasil persetujuan dari sesi. Blok ini juga menampilkan kesalahan validasi profil. [resources/views/admin/tenant.blade.php, baris 10](resources/views/admin/tenant.blade.php#L10) |
| `// 1.16` | **Perlihatkan status terbaru** | Saat halaman detail dimuat kembali, produk approved berlabel Disetujui atau Disetujui · Habis sesuai stok. [resources/views/admin/tenant.blade.php, baris 138](resources/views/admin/tenant.blade.php#L138) |

Untuk mencoba alur ini dengan data demo, buka **UD Pupuk Subur → Detail & Produk → Bibit Cabai**. Produk tersebut sudah berstatus **Menunggu**, sehingga pemeriksaan dapat dimulai langsung dari langkah 1.7.

## Bagian 2 · Masuk dan hak akses admin

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 2.1` | **Daftarkan rute admin** | Memuat rute admin dalam grup web Laravel agar formulir memakai sesi dan perlindungan CSRF. [routes/web.php, baris 44](routes/web.php#L44) |
| `// 2.2` | **Hubungkan rute dengan controller** | Mengimpor dua controller admin, pemeriksa peran dari fondasi, dan pembuat rute Laravel. [routes/admin.php, baris 7](routes/admin.php#L7) |
| `// 2.3` | **Arahkan tamu ke halaman masuk** | Mengarahkan tamu pada halaman masuk sesuai alamat yang sedang dibuka. [bootstrap/app.php, baris 18](bootstrap/app.php#L18) |
| `// 2.4` | **Sediakan rute formulir dan pengiriman** | GET menampilkan formulir masuk. POST memeriksa kredensial dengan batas percobaan dari fondasi. [routes/admin.php, baris 13](routes/admin.php#L13) |
| `// 2.5` | **Siapkan layanan autentikasi** | Memuat layanan sesi, broker token, aturan sandi, serta kedua email khusus administrator. [app/Http/Controllers/Admin/AuthController.php, baris 16](app/Http/Controllers/Admin/AuthController.php#L16) |
| `// 2.6` | **Buka formulir masuk** | Menampilkan formulir masuk khusus admin. [app/Http/Controllers/Admin/AuthController.php, baris 24](app/Http/Controllers/Admin/AuthController.php#L24) |
| `// 2.7` | **Panggil komponen logo** | Memuat logo bersama di atas formulir masuk. [resources/views/admin/login.blade.php, baris 4](resources/views/admin/login.blade.php#L4) |
| `// 2.8` | **Tampilkan identitas Bolo Tani** | Memakai identitas Bolo Tani yang sama di semua formulir admin. [resources/views/admin/partials/logo.blade.php, baris 6](resources/views/admin/partials/logo.blade.php#L6) |
| `// 2.9` | **Aktifkan tombol tampilkan sandi** | Mengaktifkan tombol tampil atau sembunyikan sandi memakai JavaScript bawaan. [resources/views/admin/login.blade.php, baris 41](resources/views/admin/login.blade.php#L41) |
| `// 2.10` | **Kirim email dan sandi** | Mengirim email dan sandi lewat POST dengan token CSRF. Tombol mata mengubah tampilan isian sandi. [resources/views/admin/login.blade.php, baris 38](resources/views/admin/login.blade.php#L38) |
| `// 2.11` | **Validasi isian login** | Memastikan email dan sandi berbentuk data yang dapat diperiksa oleh autentikasi Laravel. [app/Http/Controllers/Admin/AuthController.php, baris 32](app/Http/Controllers/Admin/AuthController.php#L32) |
| `// 2.12` | **Periksa kredensial dan buat sesi** | Memeriksa email, sandi, dan peran admin sekaligus. Sesi baru dibuat setelah berhasil masuk. [app/Http/Controllers/Admin/AuthController.php, baris 42](app/Http/Controllers/Admin/AuthController.php#L42) |
| `// 2.13` | **Tampilkan kesalahan login** | Menampilkan kesalahan masuk atau pesan sesi dari tindakan sebelumnya. [resources/views/admin/login.blade.php, baris 10](resources/views/admin/login.blade.php#L10) |
| `// 2.14` | **Arahkan pengguna yang sudah masuk** | Membawa pengguna yang sudah masuk ke dasbor perannya ketika membuka formulir khusus tamu. [bootstrap/app.php, baris 23](bootstrap/app.php#L23) |
| `// 2.15` | **Batasi halaman berdasarkan peran** | Membandingkan peran akun dengan syarat rute. Penjual yang membuka panel admin menerima HTTP 403. [app/Http/Middleware/EnsureRole.php, baris 20](app/Http/Middleware/EnsureRole.php#L20) |
| `// 2.16` | **Buat akun admin demo** | Menyiapkan email login utama pada database baru dan mempertahankan sandi jika akun admin sudah ada. [database/seeders/DatabaseSeeder.php, baris 21](database/seeders/DatabaseSeeder.php#L21) |
| `// 2.17` | **Ganti email akun lama** | Memperbarui akun admin yang sama dengan mempertahankan ID dan hash sandi. Indeks unik mencegah penimpaan bila alamat tujuan sudah dipakai. [database/migrations/2026_11_18_000001_update_primary_admin_email.php, baris 17](database/migrations/2026_11_18_000001_update_primary_admin_email.php#L17) |
| `// 2.18` | **Batalkan tautan reset sebelumnya** | Setelah alamat berubah, admin meminta tautan baru untuk email yang baru. [database/migrations/2026_11_18_000001_update_primary_admin_email.php, baris 22](database/migrations/2026_11_18_000001_update_primary_admin_email.php#L22) |

## Bagian 3 · Dashboard dan pencarian lapak

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 3.1` | **Hubungkan dashboard dengan model** | Menghubungkan panel dengan model lapak, produk, berkas foto, transaksi, dan validasi Laravel. [app/Http/Controllers/Admin/DashboardController.php, baris 14](app/Http/Controllers/Admin/DashboardController.php#L14) |
| `// 3.2` | **Muat CSS admin** | Memuat penataan layar sempit khusus admin ketika komponen digunakan untuk panel admin. [resources/views/components/layouts/dash.blade.php, baris 23](resources/views/components/layouts/dash.blade.php#L23) |
| `// 3.3` | **Atur kolom pencarian dan tabel** | Menempatkan tombol Cari di samping isian dan memungkinkan tabel lebar digeser dalam kartunya. [public/css/admin.css, baris 8](public/css/admin.css#L8) |
| `// 3.4` | **Sesuaikan tampilan pada ponsel** | Pada ponsel, navigasi pindah ke atas dan isian profil tersusun satu kolom supaya tombol aksi tetap terjangkau. [public/css/admin.css, baris 31](public/css/admin.css#L31) |
| `// 3.5` | **Kirim kata kunci pencarian** | Mencari lapak melalui dashboard, termasuk data di halaman lain. Enter atau tombol Cari mengirim kata kunci. [resources/views/components/layouts/dash.blade.php, baris 70](resources/views/components/layouts/dash.blade.php#L70) |
| `// 3.6` | **Hitung pertumbuhan lapak bulanan** | Menghitung total kumulatif per akhir bulan WIB dari lapak yang masih tercatat. Data aktif lama tanpa tanggal persetujuan memakai tanggal daftar. [app/Http/Controllers/Admin/DashboardController.php, baris 33](app/Http/Controllers/Admin/DashboardController.php#L33) |
| `// 3.7` | **Hitung produk disetujui dan menunggu** | Mengirim angka tayang dan angka menunggu secara terpisah. Stok nol tetap dihitung jika produk dan lapaknya disetujui. [app/Http/Controllers/Admin/DashboardController.php, baris 54](app/Http/Controllers/Admin/DashboardController.php#L54) |
| `// 3.8` | **Siapkan penanda toko** | Menyiapkan warna inisial toko agar baris mudah dibedakan. [resources/views/admin/dashboard.blade.php, baris 4](resources/views/admin/dashboard.blade.php#L4) |
| `// 3.9` | **Tampilkan angka ringkasan** | Menampilkan total lapak dan jumlah produk yang telah lolos persetujuan di lapak aktif. [resources/views/admin/dashboard.blade.php, baris 38](resources/views/admin/dashboard.blade.php#L38) |
| `// 3.10` | **Gambarkan tren bulanan** | Menggambar dua batang per bulan dari data controller dengan skala yang sama. [resources/views/admin/dashboard.blade.php, baris 59](resources/views/admin/dashboard.blade.php#L59) |
| `// 3.11` | **Pertahankan pencarian saat berpindah halaman** | Tautan halaman membawa kata kunci pencarian agar hasil tetap konsisten. [resources/views/admin/dashboard.blade.php, baris 104](resources/views/admin/dashboard.blade.php#L104) |

## Bagian 4 · Edit profil dan kontak lapak

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 4.1` | **Kenali lapak yang dipilih** | Menunjukkan identitas dan status lapak yang sedang diperiksa. [resources/views/admin/tenant.blade.php, baris 32](resources/views/admin/tenant.blade.php#L32) |
| `// 4.2` | **Isi kontak usaha** | Menampilkan isian kontak usaha. old() mempertahankan masukan jika validasi gagal. [resources/views/admin/tenant.blade.php, baris 58](resources/views/admin/tenant.blade.php#L58) |
| `// 4.3` | **Perbarui data profil** | Mengisi profil lapak dari data tersimpan dan mempertahankan pilihan kategori lama. [resources/views/admin/tenant.blade.php, baris 92](resources/views/admin/tenant.blade.php#L92) |
| `// 4.4` | **Kirim perubahan** | Menyimpan perubahan profil lewat PUT. Tombol hapus memakai formulir tersendiri. [resources/views/admin/tenant.blade.php, baris 160](resources/views/admin/tenant.blade.php#L160) |
| `// 4.5` | **Arahkan permintaan ke controller** | Permintaan PUT untuk satu lapak diteruskan ke update() agar data profil diperiksa. [routes/admin.php, baris 31](routes/admin.php#L31) |
| `// 4.6` | **Periksa pilihan kategori** | Menerima pilihan kategori fondasi dan mempertahankan label lama yang pernah dipakai lapak. [app/Http/Controllers/Admin/DashboardController.php, baris 74](app/Http/Controllers/Admin/DashboardController.php#L74) |
| `// 4.7` | **Validasi data profil dan kontak** | Membatasi perubahan pada isian profil dan kontak usaha. Alamat email login berada di tabel users. [app/Http/Controllers/Admin/DashboardController.php, baris 89](app/Http/Controllers/Admin/DashboardController.php#L89) |
| `// 4.8` | **Simpan dan buka kembali profil** | Menyimpan profil lalu menampilkan detail terbaru. Slug lapak tetap dapat dipakai oleh tautan pedagang. [app/Http/Controllers/Admin/DashboardController.php, baris 96](app/Http/Controllers/Admin/DashboardController.php#L96) |

## Bagian 5 · Hapus produk

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 5.1` | **Konfirmasikan penghapusan produk** | Formulir hapus produk meminta konfirmasi dan mengirim DELETE untuk produk yang dipilih. [resources/views/admin/tenant.blade.php, baris 166](resources/views/admin/tenant.blade.php#L166) |
| `// 5.2` | **Cocokkan produk dengan lapak** | Rute DELETE memastikan produk milik lapak pada URL sebelum memanggil destroyProduct(). [routes/admin.php, baris 38](routes/admin.php#L38) |
| `// 5.3` | **Hapus produk dan data foto** | Menghapus produk beserta baris foto melalui foreign key. Berkas unggahan dibersihkan setelah transaksi berhasil. [app/Http/Controllers/Admin/DashboardController.php, baris 120](app/Http/Controllers/Admin/DashboardController.php#L120) |
| `// 5.4` | **Bersihkan berkas unggahan** | Membersihkan foto unggahan dari disk publik setelah transaksi berhasil. Fungsi ini juga dipakai saat admin menghapus seluruh lapak. [app/Http/Controllers/Admin/DashboardController.php, baris 151](app/Http/Controllers/Admin/DashboardController.php#L151) |

## Bagian 6 · Hapus lapak

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 6.1` | **Konfirmasikan penghapusan lapak** | Meminta konfirmasi sebelum menghapus lapak beserta produknya. [resources/views/admin/tenant.blade.php, baris 169](resources/views/admin/tenant.blade.php#L169) |
| `// 6.2` | **Terima permintaan hapus** | Permintaan DELETE untuk lapak yang dipilih diteruskan ke destroy(). [routes/admin.php, baris 33](routes/admin.php#L33) |
| `// 6.3` | **Catat berkas dan hapus lapak beserta produk** | Menyimpan daftar berkas sebelum foreign key menghapus produk dan baris fotonya bersama lapak. [app/Http/Controllers/Admin/DashboardController.php, baris 131](app/Http/Controllers/Admin/DashboardController.php#L131) |
| `// 6.4` | **Periksa akun pemilik dan selesaikan transaksi** | Menghapus akun penjual jika lapak terakhirnya dihapus. Kegagalan transaksi membatalkan perubahan basis data. [app/Http/Controllers/Admin/DashboardController.php, baris 141](app/Http/Controllers/Admin/DashboardController.php#L141) |

## Bagian 7 · Pemulihan kata sandi

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 7.1` | **Daftarkan rute lupa sandi** | Menerima permintaan tautan pemulihan melalui email administrator. [routes/admin.php, baris 17](routes/admin.php#L17) |
| `// 7.2` | **Buka formulir lupa sandi** | Membuka formulir untuk meminta tautan pemulihan melalui email. [app/Http/Controllers/Admin/AuthController.php, baris 48](app/Http/Controllers/Admin/AuthController.php#L48) |
| `// 7.3` | **Ajukan email administrator** | Mengirim alamat email ke rute permintaan token. CSRF mengikat formulir pada sesi pengunjung. [resources/views/admin/forgot.blade.php, baris 21](resources/views/admin/forgot.blade.php#L21) |
| `// 7.4` | **Batasi frekuensi permintaan** | Membatasi permintaan pemulihan admin menjadi enam per menit per alamat IP. [app/Providers/AppServiceProvider.php, baris 31](app/Providers/AppServiceProvider.php#L31) |
| `// 7.5` | **Buat token dan kirim tautan** | Broker membuat token sementara untuk akun admin. Tautan email mengarah langsung ke formulir admin. [app/Http/Controllers/Admin/AuthController.php, baris 62](app/Http/Controllers/Admin/AuthController.php#L62) |
| `// 7.6` | **Siapkan kelas email pemulihan** | Memakai kelas email Laravel untuk menyusun subjek dan isi pesan pemulihan. [app/Mail/AdminResetLinkMail.php, baris 8](app/Mail/AdminResetLinkMail.php#L8) |
| `// 7.7` | **Bawa URL dan masa berlaku** | Membawa URL bertoken dan masa berlakunya ke template email. [app/Mail/AdminResetLinkMail.php, baris 13](app/Mail/AdminResetLinkMail.php#L13) |
| `// 7.8` | **Tetapkan subjek email** | Menentukan subjek yang terlihat di kotak masuk administrator. [app/Mail/AdminResetLinkMail.php, baris 19](app/Mail/AdminResetLinkMail.php#L19) |
| `// 7.9` | **Pilih template email** | Mengisi template Blade dengan properti publik url dan minutes di atas. [app/Mail/AdminResetLinkMail.php, baris 25](app/Mail/AdminResetLinkMail.php#L25) |
| `// 7.10` | **Tampilkan tombol pemulihan** | Menyediakan tombol yang membuka URL pemulihan unik dari broker Laravel. [resources/views/mail/admin-reset.blade.php, baris 5](resources/views/mail/admin-reset.blade.php#L5) |
| `// 7.11` | **Sediakan masa berlaku dan URL cadangan** | Menampilkan masa berlaku dan alamat yang dapat disalin jika tombol email tidak bisa dibuka. [resources/views/mail/admin-reset.blade.php, baris 8](resources/views/mail/admin-reset.blade.php#L8) |
| `// 7.12` | **Kembalikan pesan permintaan** | Mengembalikan pesan yang sama untuk semua alamat sehingga status akun tetap privat. [app/Http/Controllers/Admin/AuthController.php, baris 66](app/Http/Controllers/Admin/AuthController.php#L66) |
| `// 7.13` | **Tampilkan hasil pengajuan** | Menampilkan hasil permintaan atau pesan jika format email salah. [resources/views/admin/forgot.blade.php, baris 9](resources/views/admin/forgot.blade.php#L9) |
| `// 7.14` | **Sediakan jalan kembali ke login** | Membawa pengguna kembali ke halaman masuk admin. [resources/views/admin/forgot.blade.php, baris 25](resources/views/admin/forgot.blade.php#L25) |
| `// 7.15` | **Siapkan rute penggantian sandi** | Membuka tautan dari email, memproses sandi baru, lalu menyediakan halaman konfirmasi. [routes/admin.php, baris 22](routes/admin.php#L22) |
| `// 7.16` | **Buka formulir dari tautan email** | Membawa token dari URL dan email penerima ke formulir penggantian sandi. [app/Http/Controllers/Admin/AuthController.php, baris 72](app/Http/Controllers/Admin/AuthController.php#L72) |
| `// 7.17` | **Sertakan token dan email** | Meneruskan token dan email yang nantinya dicocokkan oleh broker Laravel. [resources/views/admin/reset.blade.php, baris 14](resources/views/admin/reset.blade.php#L14) |
| `// 7.18` | **Tunjukkan syarat sandi saat mengetik** | Memperbarui indikator persyaratan saat pengguna mengetik. Validasi akhir berjalan di controller. [resources/views/admin/reset.blade.php, baris 71](resources/views/admin/reset.blade.php#L71) |
| `// 7.19` | **Kirim sandi dan konfirmasi** | Mengirim sandi dan konfirmasi. Daftar syarat membantu admin memeriksa isian sebelum menyimpan. [resources/views/admin/reset.blade.php, baris 53](resources/views/admin/reset.blade.php#L53) |
| `// 7.20` | **Validasi sandi baru** | Memeriksa kesamaan kedua isian serta syarat minimal delapan karakter, huruf besar dan kecil, angka, dan simbol. [app/Http/Controllers/Admin/AuthController.php, baris 88](app/Http/Controllers/Admin/AuthController.php#L88) |
| `// 7.21` | **Periksa token dan simpan sandi** | Broker memeriksa token dan peran admin. Model menyimpan hash sandi baru, mengganti remember_token, lalu mengirim pemberitahuan. [app/Http/Controllers/Admin/AuthController.php, baris 98](app/Http/Controllers/Admin/AuthController.php#L98) |
| `// 7.22` | **Siapkan email pemberitahuan** | Memakai objek waktu dan fasilitas email Laravel untuk pemberitahuan perubahan sandi. [app/Mail/AdminPasswordChangedMail.php, baris 9](app/Mail/AdminPasswordChangedMail.php#L9) |
| `// 7.23` | **Sertakan nama dan waktu perubahan** | Menyediakan nama admin dan waktu perubahan bagi template email. [app/Mail/AdminPasswordChangedMail.php, baris 14](app/Mail/AdminPasswordChangedMail.php#L14) |
| `// 7.24` | **Tetapkan subjek pemberitahuan** | Menetapkan subjek pemberitahuan perubahan kata sandi. [app/Mail/AdminPasswordChangedMail.php, baris 20](app/Mail/AdminPasswordChangedMail.php#L20) |
| `// 7.25` | **Pilih template pemberitahuan** | Memilih template yang menampilkan waktu dan petunjuk bila perubahan tidak dikenali. [app/Mail/AdminPasswordChangedMail.php, baris 26](app/Mail/AdminPasswordChangedMail.php#L26) |
| `// 7.26` | **Tampilkan waktu perubahan dalam WIB** | Menyebutkan nama penerima dan waktu perubahan dalam WIB. [resources/views/mail/admin-password-changed.blade.php, baris 4](resources/views/mail/admin-password-changed.blade.php#L4) |
| `// 7.27` | **Tampilkan petunjuk jika perubahan tidak dikenali** | Memberi petunjuk bagi penerima yang tidak mengenali perubahan kata sandinya. [resources/views/mail/admin-password-changed.blade.php, baris 8](resources/views/mail/admin-password-changed.blade.php#L8) |
| `// 7.28` | **Tentukan halaman hasil reset** | Menampilkan kesalahan untuk token tidak valid atau halaman berhasil jika perubahan tersimpan. [app/Http/Controllers/Admin/AuthController.php, baris 107](app/Http/Controllers/Admin/AuthController.php#L107) |
| `// 7.29` | **Tampilkan kesalahan pada formulir** | Menampilkan kesalahan token, kata sandi, atau konfirmasi dari server. [resources/views/admin/reset.blade.php, baris 8](resources/views/admin/reset.blade.php#L8) |
| `// 7.30` | **Konfirmasikan keberhasilan dan buka login** | Mengonfirmasi penyimpanan sandi dan menyediakan tautan masuk kembali. [resources/views/admin/reset-done.blade.php, baris 12](resources/views/admin/reset-done.blade.php#L12) |

## Bagian 8 · Keluar dari akun

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 8.1` | **Tekan tombol Keluar** | Menu membuka dasbor admin dan tombol Keluar mengirim POST ke rute logout bersama. [resources/views/components/layouts/dash.blade.php, baris 43](resources/views/components/layouts/dash.blade.php#L43) |
| `// 8.2` | **Terima permintaan logout** | Rute POST keluar memanggil logout() untuk pengguna yang memiliki sesi aktif. [routes/web.php, baris 27](routes/web.php#L27) |
| `// 8.3` | **Akhiri sesi dan kembali ke login** | Logout bersama menutup sesi, memperbarui token CSRF, lalu mengembalikan admin ke formulir masuk admin. [app/Http/Controllers/AuthController.php, baris 136](app/Http/Controllers/AuthController.php#L136) |

## Bagian 9 · Pengujian fitur admin

### Urutan pembelajaran

| Langkah | Yang dipelajari | Fungsi dan lokasi kode |
| --- | --- | --- |
| `// 9.1` | **Siapkan perangkat pengujian** | Memuat model, alat uji email dan penyimpanan, serta basis pengujian Laravel. [tests/Feature/AdminPanelTest.php, baris 15](tests/Feature/AdminPanelTest.php#L15) |
| `// 9.2` | **Siapkan akun dan produk contoh** | Menyiapkan data demo terisolasi, termasuk Bibit Cabai yang menunggu persetujuan di UD Pupuk Subur. [tests/Feature/AdminPanelTest.php, baris 35](tests/Feature/AdminPanelTest.php#L35) |
| `// 9.3` | **Uji batas akses moderasi** | Membuktikan tamu diarahkan untuk masuk dan akun pedagang tidak dapat menyetujui produknya sendiri. [tests/Feature/AdminPanelTest.php, baris 47](tests/Feature/AdminPanelTest.php#L47) |
| `// 9.4` | **Uji login, pencarian, dan logout** | Menguji formulir masuk, hasil pencarian lapak, detail produk, dan penutupan sesi admin. [tests/Feature/AdminPanelTest.php, baris 59](tests/Feature/AdminPanelTest.php#L59) |
| `// 9.5` | **Uji persetujuan produk** | Memeriksa pasangan lapak-produk, persetujuan berulang, dan jumlah stok setelah moderasi. [tests/Feature/AdminPanelTest.php, baris 75](tests/Feature/AdminPanelTest.php#L75) |
| `// 9.6` | **Uji perubahan profil lapak** | Menguji bahwa profil yang tidak lengkap ditolak dan perubahan email kontak tidak mengganti identitas login. [tests/Feature/AdminPanelTest.php, baris 87](tests/Feature/AdminPanelTest.php#L87) |
| `// 9.7` | **Uji penghapusan produk dan lapak** | Menguji penghapusan unggahan produk, penghapusan berantai, dan keberadaan akun pedagang lain. [tests/Feature/AdminPanelTest.php, baris 107](tests/Feature/AdminPanelTest.php#L107) |
| `// 9.8` | **Uji tautan pemulihan sekali pakai** | Menguji pembatasan berdasarkan peran, isi email, keberhasilan penggantian sandi, dan penolakan pemakaian ulang token. [tests/Feature/AdminPanelTest.php, baris 131](tests/Feature/AdminPanelTest.php#L131) |

## Menjalankan proyek lokal
Gunakan folder yang berisi `artisan`. Dependensi di `composer.lock` memerlukan PHP 8.4.1 atau lebih baru yang kompatibel, Composer 2, dan PDO SQLite bila memakai konfigurasi contoh. Halaman admin memakai CSS dan JavaScript di folder `public`.

Untuk salinan baru dengan database demo, jalankan dari folder proyek:

```sh
composer install
php -r "if (!file_exists('.env')) copy('.env.example', '.env');"
php artisan key:generate
php -r "if (!file_exists('database/database.sqlite')) touch('database/database.sqlite');"
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Untuk proyek tim yang sudah berjalan, pertahankan `.env`, `APP_KEY`, dan database tim. Untuk menerapkan perubahan email admin pada database tim, jalankan `php artisan migrate` dari direktori proyek. Migrasi ini mempertahankan ID akun dan kata sandi yang sudah tersimpan. Seeder demo hanya untuk database latihan karena seeder fondasi dapat mengisi ulang akun dan produk contoh.

## Mencoba alur admin

1. Buka `http://127.0.0.1:8000/admin`. Sebagai tamu, Anda diarahkan ke halaman masuk admin.
2. Di database demo, masuk dengan `mohridwanramadan@gmail.com` dan kata sandi `admin`.
3. Cari **UD Pupuk Subur**, buka **Detail & Produk**, lalu cari **Bibit Cabai**. Status awal pada data demo adalah **Menunggu**.
4. Klik **Setujui**. Periksa bahwa status berubah menjadi **Disetujui**, stoknya tetap, dan jumlah produk menunggu di dashboard berkurang.
5. Coba **Lupa kata sandi** dari halaman masuk setelah keluar. Pada konfigurasi lokal bawaan, pesan berisi tautan ditulis ke `storage/logs/laravel.log`. Pengiriman ke kotak masuk memerlukan konfigurasi SMTP pada `.env`.

Pengujian fitur menggunakan SQLite dalam memori sesuai `phpunit.xml`:

```sh
php artisan test --filter=AdminPanelTest
```
