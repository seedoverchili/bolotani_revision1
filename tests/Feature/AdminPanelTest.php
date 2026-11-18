<?php

namespace Tests\Feature;

use App\Mail\AdminPasswordChangedMail;
use App\Mail\AdminResetLinkMail;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
// 9.1 — Bagian 9 · Pengujian — Siapkan perangkat pengujian. Fungsi: Memuat model, alat uji email dan penyimpanan, serta basis pengujian Laravel.

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seller;
    private Tenant $tenant;
    private Product $pending;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'mohridwanramadan@gmail.com')->firstOrFail();
        $this->seller = User::where('email', 'penjual@gmail.com')->firstOrFail();
        $this->pending = Product::where('slug', 'bibit-cabai')->firstOrFail();
        $this->tenant = $this->pending->tenant;
    }
    // 9.2 — Bagian 9 · Pengujian — Siapkan akun dan produk contoh. Fungsi: Menyiapkan data demo terisolasi, termasuk Bibit Cabai yang menunggu persetujuan di UD Pupuk Subur.

    public function test_tamu_dan_pedagang_tidak_dapat_memoderasi_produk(): void
    {
        $url = route('admin.tenant.product.approve', [$this->tenant, $this->pending]);
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->post('/admin/masuk', ['email' => $this->seller->email, 'password' => 'penjual'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($this->seller)->patch($url)->assertForbidden();
        $this->assertSame('pending', $this->pending->fresh()->status);
    }
    // 9.3 — Bagian 9 · Pengujian — Uji batas akses moderasi. Fungsi: Membuktikan tamu diarahkan untuk masuk dan akun pedagang tidak dapat menyetujui produknya sendiri.

    public function test_admin_masuk_mencari_lapak_dan_keluar(): void
    {
        $this->get('/admin/masuk')->assertOk();
        $this->post('/admin/masuk', ['email' => $this->admin->email, 'password' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get('/admin?q=Subur')->assertOk()->assertSee($this->tenant->name)->assertDontSee($this->seller->tenant->name);
        $this->get(route('admin.tenant.show', $this->tenant))->assertOk()->assertSee($this->pending->name);
        $this->post('/keluar')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
    // 9.4 — Bagian 9 · Pengujian — Uji login, pencarian, dan logout. Fungsi: Menguji formulir masuk, hasil pencarian lapak, detail produk, dan penutupan sesi admin.

    public function test_persetujuan_hanya_berlaku_untuk_produk_dalam_lapaknya(): void
    {
        $this->actingAs($this->admin);
        $other = $this->seller->tenant;
        $this->patch(route('admin.tenant.product.approve', [$other, $this->pending]))->assertNotFound();
        $this->delete(route('admin.tenant.product.destroy', [$other, $this->pending]))->assertNotFound();
        $this->assertSame('pending', $this->pending->fresh()->status);

        $url = route('admin.tenant.product.approve', [$this->tenant, $this->pending]);
        $this->patch($url)->assertRedirect(route('admin.tenant.show', $this->tenant));
        $this->patch($url)->assertRedirect();
        $this->assertSame('approved', $this->pending->fresh()->status);
        $this->assertEquals($this->pending->stock, $this->pending->fresh()->stock);
    }
    // 9.5 — Bagian 9 · Pengujian — Uji persetujuan produk. Fungsi: Memeriksa pasangan lapak-produk, persetujuan berulang, dan jumlah stok setelah moderasi.

    public function test_edit_lapak_memvalidasi_data_dan_memisahkan_email_kontak(): void
    {
        $url = route('admin.tenant.update', $this->tenant);
        $this->actingAs($this->admin)->put($url, ['email' => 'salah'])->assertSessionHasErrors(['email', 'name']);
        $input = $this->tenant->only('owner_name', 'whatsapp', 'name', 'main_category', 'kecamatan', 'address');
        $this->put($url, $input + ['email' => 'lapak@example.test'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.tenant.show', $this->tenant));
        $this->assertSame('lapak@example.test', $this->tenant->fresh()->email);
        $this->assertNotSame('lapak@example.test', $this->tenant->user->email);
    }
    // 9.6 — Bagian 9 · Pengujian — Uji perubahan profil lapak. Fungsi: Menguji bahwa profil yang tidak lengkap ditolak dan perubahan email kontak tidak mengganti identitas login.

    public function test_hapus_produk_dan_lapak_membersihkan_data_yang_tepat(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/cabai.jpg', 'contoh');
        $this->pending->images()->create(['path' => 'storage/products/cabai.jpg', 'sort' => 2]);
        $this->actingAs($this->admin)->delete(route('admin.tenant.product.destroy', [$this->tenant, $this->pending]))
            ->assertRedirect(route('admin.tenant.show', $this->tenant));
        $this->assertDatabaseMissing('products', ['id' => $this->pending->id]);
        $this->assertDatabaseMissing('product_images', ['product_id' => $this->pending->id]);
        Storage::disk('public')->assertMissing('products/cabai.jpg');
        $this->assertDatabaseHas('tenants', ['id' => $this->tenant->id]);

        $this->delete(route('admin.tenant.destroy', $this->tenant))->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('tenants', ['id' => $this->tenant->id]);
        $this->assertDatabaseMissing('products', ['tenant_id' => $this->tenant->id]);
        $this->assertDatabaseMissing('users', ['id' => $this->tenant->user_id]);
        $this->assertDatabaseHas('users', ['id' => $this->seller->id]);
    }
    // 9.7 — Bagian 9 · Pengujian — Uji penghapusan produk dan lapak. Fungsi: Menguji penghapusan unggahan produk, penghapusan berantai, dan keberadaan akun pedagang lain.

    public function test_pemulihan_sandi_mengirim_tautan_khusus_admin_sekali_pakai(): void
    {
        Mail::fake();
        $this->post('/admin/lupa-sandi', ['email' => $this->seller->email])->assertSessionHas('status');
        Mail::assertNothingSent();
        $this->get('/admin/lupa-sandi')->assertOk();
        $this->post('/admin/lupa-sandi', ['email' => $this->admin->email])->assertSessionHas('status');
        Mail::assertSent(AdminResetLinkMail::class, fn ($mail) => $mail->hasTo($this->admin->email));
        $link = Mail::sent(AdminResetLinkMail::class)->sole();
        $this->assertStringContainsString('Atur ulang kata sandi', $link->render());
        $this->get($link->url)->assertOk()->assertSee('Konfirmasi Kata Sandi Baru');
        $input = [
            'email' => $this->admin->email,
            'token' => basename(parse_url($link->url, PHP_URL_PATH)),
            'password' => 'Tani#2026Baru',
            'password_confirmation' => 'Tani#2026Baru',
        ];
        $this->post('/admin/atur-ulang-sandi', $input)->assertRedirect(route('admin.password.done'));
        $this->assertTrue(Hash::check($input['password'], $this->admin->fresh()->password));
        Mail::assertSent(AdminPasswordChangedMail::class, fn ($mail) => $mail->hasTo($this->admin->email));
        $this->post('/admin/atur-ulang-sandi', $input)->assertSessionHasErrors('password');
    }
    // 9.8 — Bagian 9 · Pengujian — Uji tautan pemulihan sekali pakai. Fungsi: Menguji pembatasan berdasarkan peran, isi email, keberhasilan penggantian sandi, dan penolakan pemakaian ulang token.
}
