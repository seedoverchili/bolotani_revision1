<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Kode OTP lupa sandi penjual. */
class OtpMail extends Mailable
{
    public const LOGIN = 'login';

    public function __construct(public string $code, public int $minutes = 10, public string $purpose = self::LOGIN) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode masuk Bolo Tani');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.otp', with: [
            'heading' => 'Kode masuk Portal Penjual',
            'intro' => 'Gunakan kode berikut untuk masuk ke Portal Penjual Bolo Tani.',
        ]);
    }
}
