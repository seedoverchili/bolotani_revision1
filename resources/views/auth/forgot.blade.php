<x-layouts.auth title="Lupa Kata Sandi — Bolo Tani">
    <h1 class="fg-title">Lupa <span>Kata Sandi?</span></h1>
    <p class="fg-sub">Masukkan email yang terdaftar untuk menerima kode verifikasi.</p>

    <div class="fg-card">
        <h2>Pulihkan akun</h2>
        <p class="sub">Gunakan gmail yang terdaftar di Bolo Tani.</p>

        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="f-label n" for="email">Email yang sudah terdaftar</label>
            <input class="f-input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            <div class="fg-hint"><x-icon name="clock" :size="11" />Kode verifikasi akan dikirim ke email anda</div>
            <button type="submit" class="btn-main dark" style="height:35px">Kirim Kode Verifikasi <x-icon name="arrow-right" :size="13" /></button>
        </form>

        <a class="fg-back" href="{{ route('login') }}"><x-icon name="arrow-left" :size="12" />Kembali ke halaman masuk</a>
    </div>
</x-layouts.auth>
