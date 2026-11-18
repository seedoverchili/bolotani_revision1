<x-layouts.auth title="Kata Sandi Berhasil Diubah — Bolo Tani" :plain="true">
    <div class="ac-card" style="text-align:center">
        <div class="ac-logo">
            <div class="mk" style="background:#1d5d22"><x-icon name="sprout" :size="20" /></div>
            <b>BOLO <span style="color:#98a69b">TANI</span></b>
            <small style="color:#8a9a8d">MARKETPLACE AGRIBISNIS LOKAL</small>
        </div>
        <div class="ok-circle"><x-icon name="check" :size="26" :stroke="3" /></div>
        <h1 style="margin-top:22px;line-height:28px">Kata Sandi<br>Berhasil Diubah</h1>
        <p class="lead" style="margin-bottom:24px">Kata sandi baru Anda telah disimpan. Silakan masuk dengan kata sandi baru.</p>
        <a class="btn-main dark" style="margin-top:0;height:44px" href="{{ route('admin.login') }}">Kembali ke Halaman Masuk</a>
        {{-- // 7.30 — Bagian 7 · Pemulihan sandi — Konfirmasikan keberhasilan dan buka login. Fungsi: Mengonfirmasi penyimpanan sandi dan menyediakan tautan masuk kembali. --}}
    </div>
</x-layouts.auth>
