<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
// 7.22 — Bagian 7 · Pemulihan sandi — Siapkan email pemberitahuan. Fungsi: Memakai objek waktu dan fasilitas email Laravel untuk pemberitahuan perubahan sandi.

class AdminPasswordChangedMail extends Mailable
{
    public function __construct(public string $name, public CarbonInterface $when) {}
    // 7.23 — Bagian 7 · Pemulihan sandi — Sertakan nama dan waktu perubahan. Fungsi: Menyediakan nama admin dan waktu perubahan bagi template email.

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kata sandi admin Bolo Tani Anda telah diubah');
    }
    // 7.24 — Bagian 7 · Pemulihan sandi — Tetapkan subjek pemberitahuan. Fungsi: Menetapkan subjek pemberitahuan perubahan kata sandi.

    public function content(): Content
    {
        return new Content(view: 'mail.admin-password-changed');
    }
    // 7.25 — Bagian 7 · Pemulihan sandi — Pilih template pemberitahuan. Fungsi: Memilih template yang menampilkan waktu dan petunjuk bila perubahan tidak dikenali.
}
