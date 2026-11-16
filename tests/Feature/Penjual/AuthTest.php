<?php

namespace Tests\Feature\Penjual;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MembuatPenjual;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use MembuatPenjual, RefreshDatabase;

    protected function setUp(): void
    {
        $this->pastikanDatabaseUji();
        parent::setUp();
    }

    private function dataDaftar(array $ubah = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@uji.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'business_name' => 'Tani Budi',
            'categories' => ['produk_pertanian'],
            'whatsapp' => '081234567890',
            'kecamatan' => 'Genteng',
            'desa' => 'Genteng Kulon',
            'address' => 'Jl. Uji No. 1',
            'lat' => '-8,3650',
            'lng' => '114.147',
            'agree' => '1',
        ], $ubah);
    }

    public function test_daftar_langsung_aktif_dan_otomatis_login(): void
    {
        $this->post(route('register.store'), $this->dataDaftar())
            ->assertRedirect(route('penjual.dashboard'));

        $this->assertAuthenticated();

        $user = User::where('email', 'budi@uji.test')->firstOrFail();
        $this->assertSame('penjual', $user->role);

        $tenant = $user->tenant;
        $this->assertSame('approved', $tenant->status);
        $this->assertMatchesRegularExpression('/^TKT-\d{4}-\d{4}$/', $tenant->code);
        $this->assertEquals(-8.365, $tenant->lat); // koma desimal diterima
        $this->assertSame(0, DB::table('password_otps')->count(), 'Registrasi tidak boleh memakai OTP.');
    }

    public function test_nomor_whatsapp_diseragamkan_ke_08(): void
    {
        $this->post(route('register.store'), $this->dataDaftar(['whatsapp' => '6281234567890']));

        $this->assertSame('081234567890', User::where('email', 'budi@uji.test')->first()->tenant->whatsapp);
    }

    public function test_email_sama_dengan_huruf_berbeda_ditolak(): void
    {
        $this->buatPenjual(['email' => 'budi@uji.test']);

        $this->post(route('register.store'), $this->dataDaftar(['email' => 'BUDI@Uji.Test']))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }

    public function test_daftar_tanpa_titik_koordinat_ditolak(): void
    {
        $this->post(route('register.store'), $this->dataDaftar(['lat' => '', 'lng' => '']))
            ->assertSessionHasErrors(['lat', 'lng']);

        $this->assertGuest();
    }

    public function test_kata_sandi_harus_kombinasi_huruf_dan_angka(): void
    {
        $this->post(route('register.store'), $this->dataDaftar([
            'password' => 'hurufsaja', 'password_confirmation' => 'hurufsaja',
        ]))->assertSessionHasErrors('password');
    }

    public function test_login_penjual_berhasil(): void
    {
        $u = $this->buatPenjual(['email' => 'a@uji.test']);

        $this->post(route('login.store'), ['email' => 'a@uji.test', 'password' => 'Rahasia123'])
            ->assertRedirect(route('penjual.dashboard'));

        $this->assertAuthenticatedAs($u);
    }

    public function test_login_tidak_membedakan_huruf_besar_pada_email(): void
    {
        $this->buatPenjual(['email' => 'a@uji.test']);

        $this->post(route('login.store'), ['email' => 'A@UJI.TEST', 'password' => 'Rahasia123']);

        $this->assertAuthenticated();
    }

    public function test_sandi_salah_ditolak(): void
    {
        $this->buatPenjual(['email' => 'a@uji.test']);

        $this->post(route('login.store'), ['email' => 'a@uji.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_akun_admin_tidak_bisa_masuk_lewat_halaman_pedagang(): void
    {
        User::create([
            'name' => 'Admin', 'email' => 'admin@uji.test', 'password' => 'Rahasia123',
            'role' => 'admin', 'email_verified_at' => now(),
        ]);

        $this->post(route('login.store'), ['email' => 'admin@uji.test', 'password' => 'Rahasia123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_tamu_dialihkan_ke_halaman_masuk(): void
    {
        $this->get(route('penjual.dashboard'))->assertRedirect(route('login'));
    }

    public function test_peran_selain_penjual_mendapat_403(): void
    {
        foreach (['admin', 'pembeli'] as $role) {
            $u = User::create([
                'name' => $role, 'email' => "{$role}@uji.test", 'password' => 'Rahasia123',
                'role' => $role, 'email_verified_at' => now(),
            ]);

            $this->actingAs($u)->get(route('penjual.dashboard'))->assertForbidden();
        }
    }

    public function test_logout(): void
    {
        $u = $this->buatPenjual();

        $this->actingAs($u)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
