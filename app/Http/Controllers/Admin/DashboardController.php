<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
// 3.1 — Bagian 3 · Dashboard — Hubungkan dashboard dengan model. Fungsi: Menghubungkan panel dengan model lapak, produk, berkas foto, transaksi, dan validasi Laravel.

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now('Asia/Jakarta')->locale('id');
        $months = collect(range(5, 0))->map(function (int $ago) use ($today) {
            $month = $today->copy()->startOfMonth()->subMonths($ago);
            $end = $month->copy()->endOfMonth()->utc();
            $registered = Tenant::where('created_at', '<=', $end);

            return [
                'label' => $month->translatedFormat('M y'),
                'registered' => (clone $registered)->count(),
                'approved' => $registered->where('status', 'approved')
                    ->where(fn ($q) => $q->whereNull('approved_at')->orWhere('approved_at', '<=', $end))->count(),
            ];
        });
        // 3.6 — Bagian 3 · Dashboard — Hitung pertumbuhan lapak bulanan. Fungsi: Menghitung total kumulatif per akhir bulan WIB dari lapak yang masih tercatat. Data aktif lama tanpa tanggal persetujuan memakai tanggal daftar.

        $search = $request->validate(['q' => ['nullable', 'string', 'max:120']])['q'] ?? '';
        $tenants = Tenant::withCount([
            'products',
            'products as pending_products_count' => fn ($query) => $query->where('status', 'pending'),
        ])->when($search !== '', function ($query) use ($search) {
            $term = '%'.$search.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhere('code', 'like', $term)->orWhere('kecamatan', 'like', $term));
        })->orderByDesc('pending_products_count')->latest()->orderByDesc('id')->paginate(10)->withQueryString();
        // 1.8 — Bagian 1 · Persetujuan produk — Urutkan lapak berdasarkan antrean. Fungsi: Mencari lapak di seluruh daftar sebelum membagi halaman. Lapak dengan antrean persetujuan terbanyak tampil dahulu.

        return view('admin.dashboard', [
            'tenantCount' => Tenant::count(),
            'productCount' => Product::approved()->whereHas('tenant', fn ($q) => $q->where('status', 'approved'))->count(),
            'pendingCount' => Product::where('status', 'pending')->count(),
            'months' => $months,
            'maxBar' => max(1, $months->max('registered')),
            'tenants' => $tenants,
        ]);
        // 3.7 — Bagian 3 · Dashboard — Hitung produk disetujui dan menunggu. Fungsi: Mengirim angka tayang dan angka menunggu secara terpisah. Stok nol tetap dihitung jika produk dan lapaknya disetujui.
    }

    public function show(Tenant $tenant)
    {
        return view('admin.tenant', [
            'tenant' => $tenant->load(['products' => fn ($q) => $q->with('images')->orderByRaw(
                'CASE WHEN status = ? THEN 0 ELSE 1 END', ['pending']
            )->latest()->orderByDesc('id')]),
            'categories' => Product::CATEGORIES,
        ]);
    }
    // 1.10 — Bagian 1 · Persetujuan produk — Muat produk dan fotonya. Fungsi: Memuat profil, daftar kategori, dan foto produk dalam satu rangkaian kueri. Produk menunggu muncul di atas untuk diperiksa.

    public function update(Request $request, Tenant $tenant)
    {
        $allowedCategories = array_values(Product::CATEGORIES);
        if ($tenant->main_category) {
            $allowedCategories[] = $tenant->main_category;
        }
        // 4.6 — Bagian 4 · Edit lapak — Periksa pilihan kategori. Fungsi: Menerima pilihan kategori fondasi dan mempertahankan label lama yang pernah dipakai lapak.

        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'whatsapp' => ['bail', 'required', 'string', 'max:20', 'regex:/^\\+?\\d[\\d -]*$/', function ($attribute, $value, $fail) {
                if (strlen(preg_replace('/\\D+/', '', $value)) < 8) {
                    $fail('Nomor WhatsApp minimal 8 angka.');
                }
            }],
            'email' => ['required', 'email', 'max:160'],
            'name' => ['required', 'string', 'max:160'],
            'main_category' => ['nullable', 'string', Rule::in($allowedCategories)],
            'kecamatan' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:500'],
        ]);
        // 4.7 — Bagian 4 · Edit lapak — Validasi data profil dan kontak. Fungsi: Membatasi perubahan pada isian profil dan kontak usaha. Alamat email login berada di tabel users.

        $data['kecamatan'] = $data['kecamatan'] ?? '';
        $tenant->update($data);

        return to_route('admin.tenant.show', $tenant)->with('ok', 'Profil lapak berhasil disimpan.');
    }
    // 4.8 — Bagian 4 · Edit lapak — Simpan dan buka kembali profil. Fungsi: Menyimpan profil lalu menampilkan detail terbaru. Slug lapak tetap dapat dipakai oleh tautan pedagang.

    public function approveProduct(Tenant $tenant, Product $product)
    {
        $changed = $tenant->products()->whereKey($product->id)->where('status', 'pending')
            ->update(['status' => 'approved']);

        return to_route('admin.tenant.show', $tenant)->with('ok', $changed
            ? "Produk {$product->name} berhasil disetujui."
            : 'Tidak ada perubahan status. Daftar produk sudah diperbarui.');
    }
    // 1.14 — Bagian 1 · Persetujuan produk — Simpan keputusan persetujuan. Fungsi: approveProduct() mengubah pending menjadi approved pada produk yang dipilih, lalu mengarahkan admin kembali ke detail lapak. Stok tetap sama.

    public function destroyProduct(Tenant $tenant, Product $product)
    {
        DB::transaction(function () use ($tenant, $product) {
            $current = $tenant->products()->lockForUpdate()->findOrFail($product->id);
            $paths = $current->images()->pluck('path')->all();
            $current->delete();
            DB::afterCommit(fn () => $this->removeUploads($paths));
        });

        return to_route('admin.tenant.show', $tenant)->with('ok', 'Produk telah dihapus.');
    }
    // 5.3 — Bagian 5 · Hapus produk — Hapus produk dan data foto. Fungsi: Menghapus produk beserta baris foto melalui foreign key. Berkas unggahan dibersihkan setelah transaksi berhasil.

    public function destroy(Tenant $tenant)
    {
        DB::transaction(function () use ($tenant) {
            $current = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $owner = $current->user()->lockForUpdate()->first();
            $paths = ProductImage::whereIn('product_id', $current->products()->select('id'))->pluck('path')->all();
            $paths[] = $current->avatar;
            $paths[] = $current->cover;
            $current->delete();
            // 6.3 — Bagian 6 · Hapus lapak — Catat berkas dan hapus lapak beserta produk. Fungsi: Menyimpan daftar berkas sebelum foreign key menghapus produk dan baris fotonya bersama lapak.

            if ($owner?->role === 'penjual' && ! Tenant::where('user_id', $owner->id)->exists()) {
                $owner->delete();
            }
            DB::afterCommit(fn () => $this->removeUploads($paths));
        });

        return to_route('admin.dashboard')->with('ok', 'Lapak dan seluruh produknya telah dihapus.');
    }
    // 6.4 — Bagian 6 · Hapus lapak — Periksa akun pemilik dan selesaikan transaksi. Fungsi: Menghapus akun penjual jika lapak terakhirnya dihapus. Kegagalan transaksi membatalkan perubahan basis data.

    private function removeUploads(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            if (Str::startsWith($path, ['storage/products/', 'storage/tenants/'])) {
                Storage::disk('public')->delete(Str::after($path, 'storage/'));
            }
        }
    }
    // 5.4 — Bagian 5 · Hapus produk — Bersihkan berkas unggahan. Fungsi: Membersihkan foto unggahan dari disk publik setelah transaksi berhasil. Fungsi ini juga dipakai saat admin menghapus seluruh lapak.
}
