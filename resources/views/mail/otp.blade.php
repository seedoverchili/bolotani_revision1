<x-mail.layout :title="$heading" :preheader="'Kode verifikasi Anda: '.$code">
    <h2 style="margin:0 0 10px;font-size:20px;line-height:26px;color:#151b2b">{{ $heading }}</h2>
    <p style="margin:0 0 4px;font-size:14px;line-height:22px;color:#374151">{{ $intro }}</p>
    <p style="margin:22px 0;text-align:center;font-size:34px;letter-spacing:10px;font-weight:800;color:#1b7a42;background:#eaf5ee;border-radius:14px;padding:16px 0">{{ $code }}</p>
    <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#374151">Kode berlaku <b>{{ $minutes }} menit</b> dan hanya dapat dipakai sekali.</p>
    <p style="margin:0;font-size:12px;line-height:18px;color:#8a93a3">Jangan bagikan kode ini kepada siapa pun, termasuk admin Bolo Tani. Jika Anda tidak merasa memintanya, abaikan email ini.</p>
</x-mail.layout>
