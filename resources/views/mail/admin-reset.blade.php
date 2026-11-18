<x-mail.layout title="Atur ulang kata sandi admin" preheader="Tautan pemulihan kata sandi, berlaku {{ $minutes }} menit.">
    <h2 style="margin:0 0 10px;font-size:20px;line-height:26px;color:#151b2b">Atur ulang kata sandi</h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:22px;color:#374151">Kami menerima permintaan pemulihan kata sandi untuk akun administrator Bolo Tani. Tekan tombol berikut untuk langsung membuat kata sandi baru.</p>
    <p style="margin:0 0 18px;text-align:center"><a href="{{ $url }}" style="display:inline-block;background:#1a5c24;color:#fff;text-decoration:none;padding:13px 26px;border-radius:999px;font-weight:700;font-size:14px">Atur Ulang Kata Sandi</a></p>
    {{-- // 7.10 — Bagian 7 · Pemulihan sandi — Tampilkan tombol pemulihan. Fungsi: Menyediakan tombol yang membuka URL pemulihan unik dari broker Laravel. --}}
    <p style="margin:0 0 6px;font-size:12.5px;line-height:19px;color:#374151">Tautan berlaku <b>{{ $minutes }} menit</b>. Jika tombol tidak berfungsi, salin tautan ini ke peramban:</p>
    <p style="margin:0 0 14px;font-size:11px;line-height:17px;color:#1b7a42;word-break:break-all">{{ $url }}</p>
    {{-- // 7.11 — Bagian 7 · Pemulihan sandi — Sediakan masa berlaku dan URL cadangan. Fungsi: Menampilkan masa berlaku dan alamat yang dapat disalin jika tombol email tidak bisa dibuka. --}}
    <p style="margin:0;font-size:12px;line-height:18px;color:#8a93a3">Jika Anda tidak merasa memintanya, abaikan email ini; kata sandi Anda tidak berubah.</p>
</x-mail.layout>
