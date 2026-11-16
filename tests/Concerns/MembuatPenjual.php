<?php

namespace Tests\Concerns;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

trait MembuatPenjual
{
    /**
     * PENGAMAN: test memakai RefreshDatabase (menghapus semua tabel). Test hanya boleh
     * jalan di SQLite memori, tidak pernah di MySQL yang berisi data. Panggil SEBELUM parent::setUp().
     */
    protected function pastikanDatabaseUji(): void
    {
        $db = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $koneksi = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION');

        if ($koneksi !== 'sqlite' || $db !== ':memory:') {
            throw new \RuntimeException(
                'DIBATALKAN: test hanya boleh memakai SQLite memori. Buka phpunit.xml dan pastikan baris '
                .'DB_CONNECTION=sqlite dan DB_DATABASE=:memory: aktif (tidak di-comment).'
            );
        }
    }

    protected function buatPenjual(array $user = [], array $tenant = []): User
    {
        $kode = Str::lower(Str::random(6));

        $u = User::create(array_merge([
            'name' => 'Penjual '.$kode,
            'email' => "penjual-{$kode}@uji.test",
            'password' => 'Rahasia123',
            'role' => 'penjual',
            'email_verified_at' => now(),
        ], $user));

        Tenant::create(array_merge([
            'user_id' => $u->id,
            'code' => 'TKT-'.Str::upper(Str::random(8)),
            'name' => 'Toko Uji '.$kode,
            'slug' => "toko-uji-{$kode}",
            'owner_name' => $u->name,
            'whatsapp' => '081200000000',
            'email' => $u->email,
            'categories' => ['produk_pertanian'],
            'main_category' => 'Produk Pertanian',
            'kecamatan' => 'Genteng',
            'desa' => 'Genteng Kulon',
            'address' => 'Jl. Uji No. 1',
            'lat' => -8.365,
            'lng' => 114.147,
            'status' => 'approved',
            'approved_at' => now(),
        ], $tenant));

        return $u->fresh();
    }

    protected function buatProduk(Tenant $tenant, array $atribut = []): Product
    {
        $produk = $tenant->products()->create(array_merge([
            'category' => 'produk_pertanian',
            'categories' => ['produk_pertanian'],
            'name' => 'Produk Uji',
            'slug' => 'produk-uji-'.Str::lower(Str::random(6)),
            'sku' => 'UJI-'.Str::upper(Str::random(4)),
            'price' => 10000,
            'unit' => 'buah',
            'stock' => 100,
            'status' => 'pending',
            'description' => 'Deskripsi uji',
        ], $atribut));

        ProductImage::create([
            'product_id' => $produk->id,
            'path' => 'storage/products/uji.jpg',
            'label' => 'Foto Utama',
            'sort' => 0,
        ]);

        return $produk;
    }
}
