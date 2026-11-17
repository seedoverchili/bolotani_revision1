<?php

namespace App\Services;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Kode OTP 6 digit via email untuk lupa sandi penjual (masuk langsung dengan kode). */
class OtpService
{
    public const MINUTES = 10;

    public const RESEND_SECONDS = 45;

    public const MAX_ATTEMPTS = 5;

    /** Buat kode baru dan kirim; mengembalikan false bila masih dalam jeda kirim ulang. */
    public function issue(string $email, string $purpose): bool
    {
        if ($this->secondsUntilResend($email, $purpose) > 0) {
            return false;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_otps')->where('email', $email)->where('purpose', $purpose)->delete();
        DB::table('password_otps')->insert([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mailer::send($email, new OtpMail($code, self::MINUTES, $purpose));

        return true;
    }

    public function secondsUntilResend(string $email, string $purpose): int
    {
        $row = DB::table('password_otps')->where('email', $email)->where('purpose', $purpose)->first();
        if (! $row) {
            return 0;
        }

        $elapsed = now()->timestamp - \Illuminate\Support\Carbon::parse($row->created_at)->timestamp;

        return max(0, self::RESEND_SECONDS - $elapsed);
    }

    /** Mengembalikan pesan galat, atau null bila kode benar (kode langsung dihabiskan). */
    public function verify(string $email, string $purpose, string $code): ?string
    {
        $row = DB::table('password_otps')->where('email', $email)->where('purpose', $purpose)->first();

        if (! $row || now()->greaterThan($row->expires_at)) {
            return 'Kode sudah kedaluwarsa. Silakan kirim ulang kode.';
        }
        if ($row->attempts >= self::MAX_ATTEMPTS) {
            return 'Terlalu banyak percobaan. Silakan kirim ulang kode.';
        }
        if (! Hash::check($code, $row->code_hash)) {
            DB::table('password_otps')->where('id', $row->id)->increment('attempts');

            return 'Kode verifikasi salah.';
        }

        DB::table('password_otps')->where('id', $row->id)->delete();

        return null;
    }
}
