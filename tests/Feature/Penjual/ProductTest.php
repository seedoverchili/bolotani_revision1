<?php

namespace Tests\Feature\Penjual;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPenjual;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use MembuatPenjual, RefreshDatabase;

    protected function setUp(): void
    {
        $this->pastikanDatabaseUji();
        parent::setUp();
        Storage::fake('public');
    }

    private function kirimProduk(User $user, array $ubah = [], int $jumlahFoto = 1)
    {
        $foto = [];
        for ($i = 0; $i < $jumlahFoto; $i++) {
            $foto[] = UploadedFile::fake()->image("foto{$i}.jpg", 200, 200);
        }

        return $this->actingAs($user)->post(route('penjual.produk.store'), array_merge([
            'categories' => ['produk_pertanian'],
            'name' => 'Bibit Uji',
            'tags' => 'Bibit, Uji',
            'price' => '15.000',
            'unit' => 'Buah',
            'description' => '<p>Bibit sehat</p>',
            'photos' => $foto,
        ], $ubah));
    }

    public function test_tambah_produk_berstatus_pending_stok_100_dan_sku_otomatis(): void
    {
        $u = $this->buatPenjual(tenant: ['name' => 'Toko Maju']);

        $this->kirimProduk($u, jumlahFoto: 2)
            ->assertRedirect(route('penjual.produk.create'))
            ->assertSessionHas('saved');

        $p = Product::firstOrFail();
        $this->assertSame('pending', $p->status);
        $this->assertSame(100, (int) $p->stock);
        $this->assertSame(15000, (int) $p->price);
        $this->assertSame('buah', $p->unit);
        $this->assertSame($u->tenant->id, $p->tenant_id);
        $this->assertMatchesRegularExpression('/^TOK-PRO-\d{4}$/', $p->sku);
        $this->assertCount(2, $p->images);

        foreach ($p->images as $gambar) {
            $this->assertStringStartsWith('storage/products/', $gambar->path);
            Storage::disk('public')->assertExists(substr($gambar->path, strlen('storage/')));
        }
    }

    public function test_deskripsi_dibersihkan_dari_script_dan_atribut(): void
    {
        $u = $this->buatPenjual();

        $this->kirimProduk($u, [
            'description' => '<p onclick="x()">Halo <script>alert(1)</script><b>tebal</b></p>',
        ]);

        $deskripsi = Product::firstOrFail()->description;
        $this->assertStringNotContainsString('<script', $deskripsi);
        $this->assertStringNotContainsString('onclick', $deskripsi);
        $this->assertStringContainsString('<b>tebal</b>', $deskripsi);
    }

    public function test_cleanHtml_menahan_berbagai_muatan_berbahaya(): void
    {
        $kasus = [
            '<p onclick="x()">Halo</p>',
            '<p/onclick=alert(1)>Halo</p>',
            '<img src=x onerror=alert(1)>',
            '<<script>script>alert(1)<</script>/script>',
            '<b onmouseover="a>b">Tebal</b>',
            '<a href="javascript:alert(1)">klik</a>',
        ];

        foreach ($kasus as $masuk) {
            $keluar = Product::cleanHtml($masuk);
            $info = "Lolos: {$masuk} => {$keluar}";

            $this->assertDoesNotMatchRegularExpression('/<\s*(script|img|a)\b/i', $keluar, $info);
            $this->assertDoesNotMatchRegularExpression('/<[^>]*\s(on\w+|href|src)\s*=/i', $keluar, $info);
            $this->assertDoesNotMatchRegularExpression('/<\w+[\s\/][^>]*>/', $keluar, $info);
        }

        $this->assertSame('<b>tebal</b> <i>miring</i>', Product::cleanHtml('<b class="x">tebal</b> <i style="y">miring</i>'));
    }

    public function test_validasi_form_produk(): void
    {
        $u = $this->buatPenjual();

        $this->kirimProduk($u, jumlahFoto: 0)->assertSessionHasErrors('photos');
        $this->kirimProduk($u, jumlahFoto: 6)->assertSessionHasErrors('photos');
        $this->kirimProduk($u, ['price' => '50'])->assertSessionHasErrors('price');
        $this->kirimProduk($u, ['price' => 'abc'])->assertSessionHasErrors('price');
        $this->kirimProduk($u, ['description' => '<div><br></div>'])->assertSessionHasErrors('description');
        $this->kirimProduk($u, ['categories' => []])->assertSessionHasErrors('categories');
        $this->kirimProduk($u, ['unit' => '<script>'])->assertSessionHasErrors('unit');

        $this->assertSame(0, Product::count());
    }

    public function test_sku_tetap_unik_setelah_ada_produk_yang_dihapus(): void
    {
        $u = $this->buatPenjual(tenant: ['name' => 'Toko Maju']);

        foreach (['A', 'B', 'C'] as $nama) {
            $this->kirimProduk($u, ['name' => "Produk {$nama}"]);
        }

        $pertama = Product::orderBy('id')->first();
        $this->actingAs($u)->delete(route('penjual.produk.destroy', $pertama))->assertRedirect();

        $this->kirimProduk($u, ['name' => 'Produk D']);

        $sku = Product::pluck('sku');
        $this->assertSame($sku->count(), $sku->unique()->count(), 'SKU kembar: '.$sku->implode(', '));
    }

    public function test_stok_hanya_bolak_balik_antara_0_dan_100(): void
    {
        $u = $this->buatPenjual();
        $p = $this->buatProduk($u->tenant);

        $this->actingAs($u)->patch(route('penjual.produk.stok', $p))->assertRedirect();
        $this->assertSame(0, (int) $p->fresh()->stock);

        $this->actingAs($u)->patch(route('penjual.produk.stok', $p))->assertRedirect();
        $this->assertSame(100, (int) $p->fresh()->stock);
    }

    public function test_pedagang_lain_tidak_boleh_mengubah_stok_produk_orang(): void
    {
        $pemilik = $this->buatPenjual();
        $orangLain = $this->buatPenjual();
        $p = $this->buatProduk($pemilik->tenant);

        $this->actingAs($orangLain)->patch(route('penjual.produk.stok', $p))->assertForbidden();

        $this->assertSame(100, (int) $p->fresh()->stock);
    }

    public function test_pedagang_lain_tidak_boleh_menghapus_produk_orang(): void
    {
        $pemilik = $this->buatPenjual();
        $orangLain = $this->buatPenjual();
        $p = $this->buatProduk($pemilik->tenant);

        $this->actingAs($orangLain)->delete(route('penjual.produk.destroy', $p))->assertForbidden();

        $this->assertNotNull(Product::find($p->id));
        $this->assertSame(1, ProductImage::where('product_id', $p->id)->count());
    }

    public function test_hapus_produk_menghapus_baris_gambar_dan_file_fotonya(): void
    {
        $u = $this->buatPenjual();
        $this->kirimProduk($u, jumlahFoto: 2);

        $p = Product::firstOrFail();
        $jalur = $p->images->pluck('path')->map(fn ($x) => substr($x, strlen('storage/')))->all();
        foreach ($jalur as $j) {
            Storage::disk('public')->assertExists($j);
        }

        $this->actingAs($u)->delete(route('penjual.produk.destroy', $p))
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertNull(Product::find($p->id));
        $this->assertSame(0, ProductImage::count());
        foreach ($jalur as $j) {
            Storage::disk('public')->assertMissing($j);
        }
    }

    public function test_tamu_tidak_bisa_menambah_produk(): void
    {
        $this->post(route('penjual.produk.store'), [])->assertRedirect(route('login'));
    }

    public function test_scope_approved_hanya_memuat_produk_yang_disetujui(): void
    {
        $u = $this->buatPenjual();
        $this->buatProduk($u->tenant, ['status' => 'pending']);
        $this->buatProduk($u->tenant, ['status' => 'approved']);

        $this->assertSame(1, Product::approved()->count());
    }

    public function test_perintah_setujui_mengubah_status_produk(): void
    {
        $u = $this->buatPenjual();
        $a = $this->buatProduk($u->tenant);
        $b = $this->buatProduk($u->tenant);

        $this->artisan('produk:setujui', ['id' => $a->id])->assertSuccessful();
        $this->assertSame('approved', $a->fresh()->status);
        $this->assertSame('pending', $b->fresh()->status);

        $this->artisan('produk:setujui', ['--semua' => true])->assertSuccessful();
        $this->assertSame('approved', $b->fresh()->status);
    }
}
