<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
// 7.6 — Bagian 7 · Pemulihan sandi — Siapkan kelas email pemulihan. Fungsi: Memakai kelas email Laravel untuk menyusun subjek dan isi pesan pemulihan.

class AdminResetLinkMail extends Mailable
{
    public function __construct(public string $url, public int $minutes) {}
    // 7.7 — Bagian 7 · Pemulihan sandi — Bawa URL dan masa berlaku. Fungsi: Membawa URL bertoken dan masa berlakunya ke template email.

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Atur ulang kata sandi admin Bolo Tani');
    }
    // 7.8 — Bagian 7 · Pemulihan sandi — Tetapkan subjek email. Fungsi: Menentukan subjek yang terlihat di kotak masuk administrator.

    public function content(): Content
    {
        return new Content(view: 'mail.admin-reset');
    }
    // 7.9 — Bagian 7 · Pemulihan sandi — Pilih template email. Fungsi: Mengisi template Blade dengan properti publik url dan minutes di atas.
}
