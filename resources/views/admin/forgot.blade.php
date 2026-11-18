<x-layouts.auth title="Lupa Kata Sandi Admin — Bolo Tani" :plain="true">
    <div class="ac-card">
        @include('admin.partials.logo')
        <h1>Lupa Kata Sandi?</h1>
        <p class="lead">Masukkan email administrator yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif
        {{-- // 7.13 — Bagian 7 · Pemulihan sandi — Tampilkan hasil pengajuan. Fungsi: Menampilkan hasil permintaan atau pesan jika format email salah. --}}

        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf
            <label class="f-label" for="email">Email Administrator</label>
            <div class="f-wrap">
                <x-icon name="user" :size="14" />
                <input class="f-input has-ic" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn-main dark" style="height:38px;margin-top:20px">Kirim Tautan Pemulihan <x-icon name="arrow-right" :size="13" />
            </button>
        </form>
        {{-- // 7.3 — Bagian 7 · Pemulihan sandi — Ajukan email administrator. Fungsi: Mengirim alamat email ke rute permintaan token. CSRF mengikat formulir pada sesi pengunjung. --}}

        <div class="ac-note"><b>Catatan Keamanan:</b> Sistem tidak akan menampilkan konfirmasi status terdaftar email untuk mencegah pemindaian akun.</div>
        <a class="ac-back" href="{{ route('admin.login') }}"><x-icon name="arrow-left" :size="11" />Kembali ke Login</a>
        {{-- // 7.14 — Bagian 7 · Pemulihan sandi — Sediakan jalan kembali ke login. Fungsi: Membawa pengguna kembali ke halaman masuk admin. --}}
    </div>
</x-layouts.auth>
