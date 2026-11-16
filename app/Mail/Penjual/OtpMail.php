<?php

namespace App\Mail\Penjual;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Email kode OTP lupa sandi pedagang. */
class OtpMail extends Mailable
{
    public function __construct(public string $code, public int $minutes = 10) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode masuk Bolo Tani');
    }

    public function content(): Content
    {
        return new Content(view: 'auth.mail-otp', with: [
            'heading' => 'Kode masuk Portal Penjual',
            'intro' => 'Gunakan kode berikut untuk masuk ke Portal Penjual Bolo Tani.',
        ]);
    }
}
