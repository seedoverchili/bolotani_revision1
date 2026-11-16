<?php

namespace App\Services\Penjual;

use App\Mail\Penjual\OtpMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Kode OTP 6 digit via email, khusus lupa sandi pedagang (kode benar = langsung masuk). */
class OtpService
{
    public const PURPOSE = 'login';

    public const MINUTES = 10;

    public const RESEND_SECONDS = 45;

    public const MAX_ATTEMPTS = 5;

    /** Buat kode baru dan kirim; false bila masih dalam jeda kirim ulang. */
    public function issue(string $email): bool
    {
        if ($this->secondsUntilResend($email) > 0) {
            return false;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($email, $code) {
            DB::table('password_otps')->where('email', $email)->where('purpose', self::PURPOSE)->delete();
            DB::table('password_otps')->insert([
                'email' => $email,
                'purpose' => self::PURPOSE,
                'code_hash' => $this->hash($email, $code),
                'expires_at' => now()->addMinutes(self::MINUTES),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // Email gagal terkirim tidak boleh membocorkan apa pun ke pengguna.
        try {
            Mail::to($email)->send(new OtpMail($code, self::MINUTES));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email OTP: '.$e->getMessage());
        }

        return true;
    }

    public function secondsUntilResend(string $email): int
    {
        $row = DB::table('password_otps')->where('email', $email)->where('purpose', self::PURPOSE)->first();
        if (! $row) {
            return 0;
        }

        $elapsed = now()->timestamp - Carbon::parse($row->created_at)->timestamp;

        return max(0, self::RESEND_SECONDS - $elapsed);
    }

    /** Mengembalikan pesan galat, atau null bila kode benar (kode langsung dihabiskan). */
    public function verify(string $email, string $code): ?string
    {
        return DB::transaction(function () use ($email, $code) {
            $row = DB::table('password_otps')
                ->where('email', $email)->where('purpose', self::PURPOSE)
                ->lockForUpdate()->first();

            if (! $row || now()->greaterThan(Carbon::parse($row->expires_at))) {
                return 'Kode sudah kedaluwarsa. Silakan kirim ulang kode.';
            }
            if ($row->attempts >= self::MAX_ATTEMPTS) {
                return 'Terlalu banyak percobaan. Silakan kirim ulang kode.';
            }
            if (! hash_equals($row->code_hash, $this->hash($email, $code))) {
                // expires_at diisi dirinya sendiri: di MariaDB kolom timestamp pertama otomatis
                // ikut "ON UPDATE current_timestamp" dan masa berlaku kode akan ikut bergeser.
                DB::table('password_otps')->where('id', $row->id)->update([
                    'attempts' => DB::raw('attempts + 1'),
                    'expires_at' => DB::raw('expires_at'),
                ]);

                return 'Kode verifikasi salah.';
            }

            DB::table('password_otps')->where('id', $row->id)->delete();

            return null;
        });
    }

    /** HMAC dengan kunci aplikasi: bocornya tabel saja tidak cukup untuk menebak 6 digit. */
    private function hash(string $email, string $code): string
    {
        return hash_hmac('sha256', $email.'|'.$code, (string) config('app.key'));
    }
}
