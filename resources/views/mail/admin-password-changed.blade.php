<x-mail.layout title="Kata sandi admin diubah" preheader="Kata sandi akun admin Anda baru saja diubah.">
    <h2 style="margin:0 0 10px;font-size:20px;line-height:26px;color:#151b2b">Kata sandi Anda telah diubah</h2>
    <p style="margin:0 0 14px;font-size:14px;line-height:22px;color:#374151">Halo {{ $name }}, kata sandi akun administrator Bolo Tani Anda baru saja diubah pada <b>{{ $when->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }} WIB</b>.</p>
    {{-- // 7.26 — Bagian 7 · Pemulihan sandi — Tampilkan waktu perubahan dalam WIB. Fungsi: Menyebutkan nama penerima dan waktu perubahan dalam WIB. --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fdecec;border:1px solid #f6c7c7;border-radius:12px"><tr><td style="padding:14px 16px;font-size:12.5px;line-height:20px;color:#991b1b">
        <b>Bukan Anda?</b> Segera minta tautan pemulihan baru dan hubungi admin utama, karena akun Anda mungkin diakses pihak lain.
    </td></tr></table>
    {{-- // 7.27 — Bagian 7 · Pemulihan sandi — Tampilkan petunjuk jika perubahan tidak dikenali. Fungsi: Memberi petunjuk bagi penerima yang tidak mengenali perubahan kata sandinya. --}}
</x-mail.layout>
