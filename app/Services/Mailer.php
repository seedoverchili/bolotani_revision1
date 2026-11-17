<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Pengiriman email yang gagal tidak boleh menggagalkan aksi utama (daftar, konfirmasi, hapus). */
class Mailer
{
    /** @param  string|array<int, string>  $to */
    public static function send(string|array $to, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email '.$mailable::class.': '.$e->getMessage());

            return false;
        }
    }
}
