<?php

namespace Tests\Feature\Penjual;

use App\Mail\Penjual\OtpMail;
use App\Models\User;
use App\Services\Penjual\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MembuatPenjual;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use MembuatPenjual, RefreshDatabase;

    protected function setUp(): void
    {
        $this->pastikanDatabaseUji();
        parent::setUp();
    }

    /** Membuat kode lewat service dan mengambil kodenya dari email yang "terkirim". */
    private function mintaKode(string $email): string
    {
        Mail::fake();
        app(OtpService::class)->issue($email);

        $kode = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$kode) {
            $kode = $mail->code;

            return true;
        });

        return $kode;
    }

    private function kirimKode(string $email, string $kode)
    {
        return $this->withSession(['otp_email' => $email, 'otp_sent_at' => now()->timestamp])
            ->post(route('password.otp.verify'), ['code' => $kode]);
    }

    private function kodeSalah(string $benar): string
    {
        return $benar === '111111' ? '222222' : '111111';
    }

    public function test_email_terdaftar_menerima_kode_dan_kode_disimpan_sebagai_hash(): void
    {
        Mail::fake();
        $this->buatPenjual(['email' => 'p@uji.test']);

        $this->post(route('password.email'), ['email' => '  P@Uji.Test '])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('otp_email', 'p@uji.test');

        Mail::assertSent(OtpMail::class, fn (OtpMail $m) => $m->hasTo('p@uji.test'));

        $baris = DB::table('password_otps')->first();
        $this->assertSame('p@uji.test', $baris->email);
        $this->assertSame(64, strlen($baris->code_hash));
        $this->assertSame(0, (int) $baris->attempts);
    }

    public function test_email_tidak_terdaftar_dan_akun_admin_tidak_dikirimi_kode(): void
    {
        Mail::fake();
        User::create([
            'name' => 'Admin', 'email' => 'admin@uji.test', 'password' => 'Rahasia123',
            'role' => 'admin', 'email_verified_at' => now(),
        ]);

        foreach (['tidakada@uji.test', 'admin@uji.test'] as $email) {
            // Tampilan sama persis dengan email terdaftar, supaya akun tidak bisa ditebak.
            $this->post(route('password.email'), ['email' => $email])
                ->assertRedirect(route('password.otp'))
                ->assertSessionHas('status');
        }

        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('password_otps')->count());
    }

    public function test_halaman_kode_tanpa_permintaan_sebelumnya_404(): void
    {
        $this->get(route('password.otp'))->assertNotFound();
    }

    public function test_kode_benar_langsung_masuk_dan_kode_dihabiskan(): void
    {
        $u = $this->buatPenjual(['email' => 'p@uji.test']);
        $kode = $this->mintaKode('p@uji.test');

        $this->kirimKode('p@uji.test', $kode)->assertRedirect(route('penjual.dashboard'));

        $this->assertAuthenticatedAs($u);
        $this->assertSame(0, DB::table('password_otps')->count(), 'Kode harus sekali pakai.');
    }

    public function test_format_kode_harus_6_digit(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $this->mintaKode('p@uji.test');

        $this->kirimKode('p@uji.test', 'abc')->assertSessionHasErrors('code');
        $this->kirimKode('p@uji.test', '12345')->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_kode_salah_menambah_percobaan_tanpa_menggeser_masa_berlaku(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $benar = $this->mintaKode('p@uji.test');
        $kedaluwarsaAwal = DB::table('password_otps')->value('expires_at');

        $this->kirimKode('p@uji.test', $this->kodeSalah($benar))->assertSessionHasErrors('code');
        $this->kirimKode('p@uji.test', $this->kodeSalah($benar))->assertSessionHasErrors('code');

        $this->assertSame(2, (int) DB::table('password_otps')->value('attempts'));
        $this->assertSame($kedaluwarsaAwal, DB::table('password_otps')->value('expires_at'));
        $this->assertGuest();
    }

    public function test_setelah_5_kali_salah_kode_benar_pun_ditolak(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $benar = $this->mintaKode('p@uji.test');

        for ($i = 0; $i < 5; $i++) {
            $this->kirimKode('p@uji.test', $this->kodeSalah($benar));
        }

        $this->kirimKode('p@uji.test', $benar)
            ->assertSessionHasErrors(['code' => 'Terlalu banyak percobaan. Silakan kirim ulang kode.']);

        $this->assertGuest();
    }

    public function test_kode_kedaluwarsa_ditolak(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $benar = $this->mintaKode('p@uji.test');

        DB::table('password_otps')->update(['expires_at' => now()->subMinute()]);

        $this->kirimKode('p@uji.test', $benar)
            ->assertSessionHasErrors(['code' => 'Kode sudah kedaluwarsa. Silakan kirim ulang kode.']);

        $this->assertGuest();
    }

    public function test_kirim_ulang_dalam_jeda_45_detik_tidak_mengirim_email_baru(): void
    {
        Mail::fake();
        $this->buatPenjual(['email' => 'p@uji.test']);

        $this->post(route('password.email'), ['email' => 'p@uji.test']);
        $this->post(route('password.email'), ['email' => 'p@uji.test'])
            ->assertRedirect(route('password.otp'));

        Mail::assertSent(OtpMail::class, 1);
    }

    public function test_kirim_ulang_setelah_jeda_membuat_kode_baru_dan_membatalkan_yang_lama(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $lama = $this->mintaKode('p@uji.test');

        // Anggap 46 detik sudah berlalu.
        DB::table('password_otps')->update(['created_at' => now()->subSeconds(46)]);

        $baru = $this->mintaKode('p@uji.test');

        $this->assertSame(1, DB::table('password_otps')->count());
        if ($lama !== $baru) {
            $this->kirimKode('p@uji.test', $lama)->assertSessionHasErrors('code');
        }
    }

    public function test_batas_kirim_kode_per_ip(): void
    {
        Mail::fake();

        for ($i = 1; $i <= 6; $i++) {
            $this->post(route('password.email'), ['email' => "x{$i}@uji.test"])->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'x7@uji.test'])->assertStatus(429);
    }

    public function test_batas_kirim_kode_per_email(): void
    {
        Mail::fake();

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('password.email'), ['email' => 'korban@uji.test'])->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'korban@uji.test'])->assertStatus(429);
    }

    public function test_batas_verifikasi_kode_per_ip(): void
    {
        $this->buatPenjual(['email' => 'p@uji.test']);
        $benar = $this->mintaKode('p@uji.test');

        for ($i = 1; $i <= 10; $i++) {
            $this->kirimKode('p@uji.test', $this->kodeSalah($benar));
        }

        $this->kirimKode('p@uji.test', $this->kodeSalah($benar))->assertStatus(429);
    }
}
