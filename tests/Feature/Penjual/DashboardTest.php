<?php

namespace Tests\Feature\Penjual;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPenjual;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use MembuatPenjual, RefreshDatabase;

    protected function setUp(): void
    {
        $this->pastikanDatabaseUji();
        parent::setUp();
        Storage::fake('public');
    }

    public function test_dashboard_hanya_menampilkan_produk_milik_sendiri(): void
    {
        $a = $this->buatPenjual(tenant: ['name' => 'Toko Alfa']);
        $b = $this->buatPenjual();
        $this->buatProduk($a->tenant, ['name' => 'Produk Milik Alfa']);
        $this->buatProduk($b->tenant, ['name' => 'Produk Milik Beta']);

        $this->actingAs($a)->get(route('penjual.dashboard'))
            ->assertOk()
            ->assertSee('Toko Alfa')
            ->assertSee('Produk Milik Alfa')
            ->assertDontSee('Produk Milik Beta');
    }

    public function test_profil_toko_dapat_diperbarui(): void
    {
        $u = $this->buatPenjual();

        $this->actingAs($u)->put(route('penjual.profil.update'), [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru',
            'main_category' => 'Produk Pertanian',
            'address' => 'Alamat baru',
            'lat' => '-8,2',
            'lng' => '114.1',
            'kecamatan' => 'Genteng',
            'desa' => 'Genteng Kulon',
        ])->assertRedirect();

        $tenant = $u->tenant->fresh();
        $this->assertSame('Nama Baru', $tenant->name);
        $this->assertSame('Alamat baru', $tenant->address);
        $this->assertEquals(-8.2, $tenant->lat);
    }

    public function test_profil_tanpa_alamat_ditolak_dan_data_lama_aman(): void
    {
        $u = $this->buatPenjual(tenant: ['name' => 'Nama Lama']);

        $this->actingAs($u)->put(route('penjual.profil.update'), [
            'name' => 'Nama Baru', 'address' => '', 'lat' => '-8.2', 'lng' => '114.1',
        ])->assertSessionHasErrors('address');

        $this->assertSame('Nama Lama', $u->tenant->fresh()->name);
    }

    public function test_update_profil_tidak_menyentuh_toko_orang_lain(): void
    {
        $a = $this->buatPenjual();
        $b = $this->buatPenjual(tenant: ['name' => 'Toko Beta']);

        $this->actingAs($a)->put(route('penjual.profil.update'), [
            'name' => 'Diubah Alfa', 'address' => 'x', 'lat' => '-8.2', 'lng' => '114.1',
        ]);

        $this->assertSame('Toko Beta', $b->tenant->fresh()->name);
    }

    public function test_unggah_foto_dan_foto_lama_terhapus(): void
    {
        $u = $this->buatPenjual();

        $this->actingAs($u)->put(route('penjual.foto.update'), [
            'avatar' => UploadedFile::fake()->image('a.jpg', 100, 100),
            'cover_pos' => 50,
        ])->assertRedirect(route('penjual.dashboard'));

        $pertama = $u->tenant->fresh()->avatar;
        $this->assertStringStartsWith('storage/tenants/', $pertama);
        Storage::disk('public')->assertExists(substr($pertama, strlen('storage/')));

        $this->actingAs($u)->put(route('penjual.foto.update'), [
            'avatar' => UploadedFile::fake()->image('b.jpg', 100, 100),
        ]);

        $kedua = $u->tenant->fresh()->avatar;
        $this->assertNotSame($pertama, $kedua);
        Storage::disk('public')->assertMissing(substr($pertama, strlen('storage/')));
        Storage::disk('public')->assertExists(substr($kedua, strlen('storage/')));
    }

    public function test_foto_bukan_gambar_ditolak(): void
    {
        $u = $this->buatPenjual();

        $this->actingAs($u)->put(route('penjual.foto.update'), [
            'avatar' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('avatar');
    }

    public function test_avatar_lebih_dari_2mb_ditolak(): void
    {
        $u = $this->buatPenjual();

        $this->actingAs($u)->put(route('penjual.foto.update'), [
            'avatar' => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3000),
        ])->assertSessionHasErrors('avatar');
    }
}
