<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun per role (email/nama/password = nama role)
        User::updateOrCreate(['email' => 'admin@gmail.com'], ['name' => 'admin', 'password' => 'admin', 'role' => 'admin', 'email_verified_at' => now()]);
        User::updateOrCreate(
            ['email' => 'budi.santoso@bolotani.banyuwangikab.go.id'],
            ['name' => 'Budi Santoso', 'password' => 'admin', 'role' => 'admin', 'email_verified_at' => now()]
        );
        $penjual = User::updateOrCreate(['email' => 'penjual@gmail.com'], ['name' => 'penjual', 'password' => 'penjual', 'role' => 'penjual', 'email_verified_at' => now()]);

        $this->seedMarketplaceTenants();
        $this->seedRohmad($penjual);
        $this->markOutOfStock();
        $this->seedAdminDemoTenants();
        $this->seedRelated();
    }

    private function tenant(array $attrs, ?User $user = null): Tenant
    {
        $user ??= User::updateOrCreate(
            ['email' => $attrs['email']],
            ['name' => $attrs['owner_name'], 'password' => 'password', 'role' => 'penjual', 'email_verified_at' => now()]
        );

        return Tenant::updateOrCreate(['code' => $attrs['code']], $attrs + [
            'user_id' => $user->id,
            'slug' => Str::slug($attrs['name']),
            'status' => 'approved',
            'approved_at' => now(),
            'email_sent_at' => now(),
        ] + $this->coordinates($attrs));
    }

    /** Koordinat perkiraan per kecamatan (tenant yang sekecamatan digeser sedikit agar pin tidak menumpuk). */
    private function coordinates(array $attrs): array
    {
        if (isset($attrs['lat'], $attrs['lng'])) {
            return [];
        }
        $centers = [
            'Kabat' => [-8.2780, 114.3010], 'Songgon' => [-8.3380, 114.1950], 'Rogojampi' => [-8.3260, 114.3030],
            'Glagah' => [-8.2180, 114.3340], 'Giri' => [-8.2090, 114.3680], 'Genteng' => [-8.3650, 114.1470],
        ];
        [$lat, $lng] = $centers[$attrs['kecamatan'] ?? ''] ?? [-8.2192, 114.3267];
        $shift = (crc32($attrs['code'] ?? '') % 20) / 5000;

        return ['lat' => round($lat + $shift, 7), 'lng' => round($lng + $shift, 7)];
    }

    private function product(Tenant $tenant, array $attrs, array $images): Product
    {
        $product = Product::updateOrCreate(
            ['slug' => $attrs['slug'] ?? Str::slug($attrs['name'])],
            $attrs + ['tenant_id' => $tenant->id, 'stock' => 100, 'status' => 'approved']
        );
        $product->images()->delete();
        foreach (array_values($images) as $i => $path) {
            ProductImage::create(['product_id' => $product->id, 'path' => $path, 'sort' => $i]);
        }

        return $product;
    }

    private function seedMarketplaceTenants(): void
    {
        $t = fn (string $code, string $name, string $owner, string $kec, string $email) => $this->tenant([
            'code' => $code, 'name' => $name, 'owner_name' => $owner, 'whatsapp' => '081234567890',
            'email' => $email, 'kecamatan' => $kec, 'desa' => $kec, 'address' => "Kecamatan $kec, Kabupaten Banyuwangi",
            'categories' => ['produk_pertanian'], 'main_category' => 'Pembibitan',
        ]);

        $agro = $t('TKT-2025-0801', 'CV. Agro Kalibening', 'Agus Santoso', 'Kabat', 'agro@example.com');
        $kwt = $t('TKT-2025-0802', 'KWT Makmur Sentosa', 'Siti Aminah', 'Songgon', 'kwt@example.com');
        $herbal = $t('TKT-2025-0803', 'Herbal Cindhani', 'Dewi Lestari', 'Rogojampi', 'herbal@example.com');
        $green = $t('TKT-2025-0804', 'Green Bravo Agrotech', 'Bravo Pratama', 'Glagah', 'green@example.com');
        $sahabat = $t('TKT-2025-0805', 'Sahabat Tani Glagah', 'Joko Widodo', 'Glagah', 'sahabat@example.com');
        $kohar = $t('TKT-2025-0806', 'Abdul Kohar', 'Abdul Kohar', 'Giri', 'kohar@example.com');
        $riyeoul = $t('TKT-2025-0807', 'Riyeoul Organik', 'Riyeoul', 'Genteng', 'riyeoul@example.com');

        $p = 'images/products/';
        $this->product($agro, ['category' => 'produk_pertanian', 'name' => 'Bibit Alpukat Kalibening', 'slug' => 'bibit-alpukat-kalibening-cv', 'sku' => 'AGR-ALP-001', 'price' => 35000, 'unit' => 'pohon', 'description' => 'Bibit okulasi unggul sertifikasi BPSB.'], [$p.'bibit-alpukat-kalibening.jpg']);
        $this->product($kwt, ['category' => 'hasil_pertanian', 'name' => 'Jamur Kuping Organik', 'sku' => 'KWT-JMR-001', 'price' => 15000, 'unit' => 'kg', 'description' => 'Budidaya baglog serbuk kayu tanpa bahan kimia.'], [$p.'jamur-kuping-organik.jpg']);
        $this->product($herbal, ['category' => 'produk_olahan', 'name' => 'Wedang Uwuh Telang', 'sku' => 'HRB-WDG-001', 'price' => 15000, 'unit' => 'pouch', 'description' => 'Racikan rempah alami kaya antioksidan.'], [$p.'wedang-uwuh-telang.jpg']);
        $this->product($green, ['category' => 'produk_pertanian', 'name' => 'Bio Pukace Hayati 1L', 'sku' => 'GBA-PKC-001', 'price' => 17000, 'unit' => 'botol', 'description' => 'Dekomposer dan pembenah hara tanah organik.'], [$p.'bio-pukace-hayati.jpg']);
        $this->product($sahabat, ['category' => 'produk_olahan', 'name' => 'Sirup Jahe Pilihan', 'sku' => 'STG-SJH-001', 'price' => 38000, 'unit' => 'botol 500ml', 'description' => 'Ekstrak jahe merah murni dengan gula aren.'], [$p.'sirup-jahe-pilihan.jpg']);
        $this->product($sahabat, ['category' => 'alat_pertanian', 'name' => 'Sprayer Elektrik 16L', 'sku' => 'STG-SPR-001', 'price' => 285000, 'unit' => 'unit', 'description' => 'Sprayer elektrik baterai tahan lama.'], [$p.'bio-pukace-hayati.jpg']);
    }

    private function seedRohmad(User $penjual): void
    {
        $rohmad = $this->tenant([
            'code' => 'TKT-2025-0001', 'name' => 'Kebun Bibit ROHMAD', 'owner_name' => 'Rohmad Hidayat',
            'whatsapp' => '0881036094520', 'email' => 'penjual@gmail.com', 'kecamatan' => 'Kabat', 'desa' => 'Kalibening',
            'address' => 'Dusun Krajan, Desa Kalibening RT 02 / RW 01, Kec. Kabat, Kab. Banyuwangi (Dekat Poliwangi)',
            'categories' => ['produk_pertanian'], 'main_category' => 'Pembibitan & Tanaman Buah',
            'description' => 'Pusat pembibitan alpukat varietas unggul bersertifikat, bibit buah tropis okulasi, dan edukasi budidaya tani terpadu di Banyuwangi. Melayani pengiriman ke seluruh Pulau Jawa & Bali dengan garansi keaslian varietas dan penanganan pascapanen tersertifikasi.',
            'avatar' => 'images/tenants/rohmad-avatar.jpg', 'cover' => 'images/tenants/rohmad-cover.jpg',
            'wa_clicks' => 348,
        ], $penjual);

        $p = 'images/products/';
        $m3 = [$p.'m3-alpukat-super.jpg', $p.'m3-durian-bawor.jpg', $p.'m3-jambu-kristal.jpg', $p.'m3-alpukat-miki.jpg'];
        $rows = [
            ['Bibit Alpukat Kalibening Super', 'BLT-ALP-002', 'Bibit Alpukat', 'Bibit Alpukat', 'Okulasi Indukan BPSB', 35000, 'polybag', 'Indukan vegetatif sambung pucuk adaptif dataran rendah-tinggi. Daging pulen, gurih mentega tanpa serat tebal.', 'Varietas Unggulan Banyuwangi', 0, [$m3[0]]],
            ['Bibit Durian Bawor Banyuwangi', 'BLT-DUR-004', 'Bibit Durian', 'Bibit Durian', 'Kaki 3 Okulasi', 65000, 'pohon', 'Akar ganda kokoh tahan roboh angin kencang. Daging oranye tebal, biji kempes, berbuah usia 3-4 tahun.', 'Siap Tanam Kebun', 1, [$m3[1]]],
            ['Jambu Kristal Getas Merah Kabat', 'BLT-JMB-012', 'Tanaman Buah Tropis', 'Tanaman Buah', 'Cangkok Super', 25000, 'pohon', 'Daging renyah manis tanpa biji merepotkan. Sangat cocok untuk pekarangan rumah maupun perkebunan intensif.', 'Genjah / Cepat Berbuah', 2, [$m3[2]]],
            ['Bibit Alpukat Miki Dataran Rendah', 'BLT-ALP-003', 'Bibit Alpukat', 'Bibit Alpukat', 'Sambung Pucuk', 40000, 'pohon', 'Kulit tebal anti ulat daun dan lalat buah. Sangat digemari di Banyuwangi pesisir hingga perbukitan Rogojampi.', 'Tahan Ulat Buah', 3, [$m3[3]]],
            ['Bibit Alpukat Aligator', 'BLT-ALP-004', 'Bibit Alpukat', 'Bibit Alpukat', 'Sambung Pucuk', 45000, 'pohon', 'Buah besar berdaging tebal, cocok untuk dataran menengah.', null, 0, [$m3[0]]],
            ['Bibit Durian Musang King', 'BLT-DUR-005', 'Bibit Durian', 'Bibit Durian', 'Okulasi', 150000, 'pohon', 'Durian premium daging kuning pekat rasa pahit manis.', null, 1, [$m3[1]]],
            ['Bibit Durian Montong', 'BLT-DUR-006', 'Bibit Durian', 'Bibit Durian', 'Okulasi', 85000, 'pohon', 'Durian daging tebal tahan penyakit.', null, 1, [$m3[1]]],
            ['Bibit Jeruk Pamelo Merah', 'BLT-JRK-007', 'Tanaman Buah Tropis', 'Tanaman Buah', 'Cangkok', 55000, 'pohon', 'Pamelo merah manis segar berbuah cepat.', null, 2, [$m3[2]]],
            ['Bibit Mangga Arumanis', 'BLT-MGG-008', 'Tanaman Buah Tropis', 'Tanaman Buah', 'Sambung Pucuk', 40000, 'pohon', 'Mangga arumanis khas Probolinggo-Banyuwangi.', null, 2, [$m3[2]]],
            ['Pupuk Kandang Fermentasi 5kg', 'BLT-PPK-009', 'Pupuk & Media', 'Pupuk & Media', 'Organik', 22000, 'karung 5kg', 'Pupuk kandang matang fermentasi siap pakai.', null, 3, [$m3[3]]],
            ['Media Tanam Siap Pakai', 'BLT-MDT-010', 'Pupuk & Media', 'Pupuk & Media', 'Campuran', 18000, 'karung', 'Campuran tanah, sekam bakar, dan kompos.', null, 3, [$m3[3]]],
            ['Bibit Alpukat Kalibening', 'BLT-ALP-001', 'Bibit Alpukat', 'Bibit Alpukat', 'Okulasi Indukan BPSB', 35000, 'polybag pohon', 'Bibit indukan vegetatif sambung pucuk (okulasi) bersertifikasi pemuliaan lokal Desa Kalibening, adaptif di dataran rendah maupun tinggi dengan tekstur daging pulen mentega tanpa serat.', null, 0, [$p.'alpukat-1-daun.jpg', $p.'alpukat-3-sambung.jpg', $p.'alpukat-4-buah.jpg', $p.'alpukat-5-area.jpg']],
        ];
        foreach ($rows as [$name, $sku, $group, $sub, $tags, $price, $unit, $desc, $badge, $tone, $imgs]) {
            $this->product($rohmad, [
                'category' => 'produk_pertanian', 'name' => $name, 'sku' => $sku, 'group' => $group,
                'subcategory' => $sub, 'tags' => $tags, 'price' => $price, 'unit' => $unit,
                'description' => $desc, 'badge' => $badge, 'badge_tone' => $tone,
                'is_featured' => $name === 'Bibit Alpukat Kalibening Super',
            ], $imgs);
        }
    }

    private function markOutOfStock(): void
    {
        Product::whereIn('sku', ['BLT-PPK-009', 'BLT-MDT-010'])->update(['stock' => 0]);
    }

    private function seedAdminDemoTenants(): void
    {
        $makmur = $this->tenant([
            'code' => 'TKT-2025-0891', 'name' => 'Toko Tani Makmur', 'owner_name' => 'Rahmat Hidayat',
            'whatsapp' => '081200001234', 'email' => 'tanimakmur@example.com', 'kecamatan' => 'Rogojampi',
            'address' => 'Desa Rogojampi, Kecamatan Rogojampi, Kabupaten Banyuwangi.',
            'categories' => ['produk_pertanian'], 'main_category' => 'Produk Pertanian',
        ]);
        $makmur->update(['created_at' => '2025-03-14 09:42:00']);

        $subur = $this->tenant([
            'code' => 'TKT-2025-0890', 'name' => 'UD Pupuk Subur', 'owner_name' => 'Rahmat Hidayat',
            'whatsapp' => '081200001234', 'email' => 'tanimakmur2@example.com', 'kecamatan' => 'Kabat',
            'address' => 'Desa Rogojampi, Kecamatan Rogojampi, Kabupaten Banyuwangi.',
            'categories' => ['produk_pertanian'], 'main_category' => 'Produk Pertanian',
        ]);
        $subur->update(['created_at' => '2025-03-13 16:30:00']);
        $p = 'images/products/';
        $this->product($subur, ['category' => 'produk_pertanian', 'name' => 'Bibit Alpukat Subur', 'sku' => 'TM-001', 'price' => 35000, 'unit' => 'batang', 'stock' => 120], [$p.'bibit-alpukat-kalibening.jpg']);
        $this->product($subur, ['category' => 'produk_pertanian', 'name' => 'Bibit Cabai', 'sku' => 'TM-002', 'price' => 5000, 'unit' => 'polybag', 'stock' => 200, 'status' => 'pending'], [$p.'alpukat-5-area.jpg']);
    }

    private function seedRelated(): void
    {
        $kohar = Tenant::where('code', 'TKT-2025-0806')->first();
        $green = Tenant::where('code', 'TKT-2025-0804')->first();
        $riyeoul = Tenant::where('code', 'TKT-2025-0807')->first();
        $p = 'images/products/';
        $this->product($kohar, ['category' => 'hasil_pertanian', 'name' => 'Kaktus Mini Hias', 'sku' => 'AKH-KKT-001', 'price' => 10000, 'unit' => 'buah', 'description' => 'Kaktus mini untuk dekorasi meja dan taman.'], [$p.'kaktus-mini.jpg']);
        $this->product($green, ['category' => 'produk_pertanian', 'name' => 'Bio Pukace Probiotik', 'sku' => 'GBA-PKC-002', 'price' => 17000, 'unit' => 'liter', 'description' => 'Probiotik cair untuk pupuk organik.'], [$p.'bio-pukace-probiotik.jpg']);
        $this->product($riyeoul, ['category' => 'hasil_pertanian', 'name' => 'Jamur Lingzhi Herbal', 'sku' => 'RYO-JLZ-001', 'price' => 10000, 'unit' => 'pack', 'description' => 'Jamur lingzhi kering untuk seduhan herbal.'], [$p.'jamur-lingzhi.jpg']);
    }
}
