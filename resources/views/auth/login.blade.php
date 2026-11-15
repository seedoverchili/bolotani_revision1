<x-layouts.auth title="Masuk Penjual — Bolo Tani">
    <h1 class="l-title">Masuk ke Portal Penjual <span>Bolo Tani</span></h1>
    <p class="l-sub">Kelola katalog komoditas, sertifikasi mutu, dan pesanan WhatsApp dalam satu dasbor.</p>

    <div class="l-card">
        <h2>Masuk ke akun Anda</h2>
        <p class="sub">Pilih cara masuk yang Anda daftarkan di Bolo Tani.</p>

        <div class="l-mode"><x-icon name="mail" :size="13" />Masuk dengan Email dan kata sandi</div>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="r-field">
                <label class="f-label n" for="email">Email</label>
                <input class="f-input" style="height:44px;border-radius:12px" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>
            <div class="r-field" style="margin-bottom:0">
                <label class="f-label n" for="password">Kata sandi <i>*</i></label>
                <div class="f-wrap">
                    <x-icon name="lock-key" :size="14" />
                    <input class="f-input has-ic" id="password" name="password" type="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                    <button type="button" class="eye" data-eye="password" aria-label="Tampilkan kata sandi"><span class="on"><x-icon name="eye" :size="15" /></span><span class="off" style="display:none"><x-icon name="eye-off" :size="15" /></span></button>
                </div>
            </div>
            <div class="l-row"><a class="link-g" href="{{ route('password.request') }}">Lupa kata sandi?</a></div>
            <button type="submit" class="btn-main">Masuk <x-icon name="arrow-right" :size="14" /></button>
        </form>

        <div class="l-foot">Belum punya lapak? <a class="link-g" href="{{ route('register') }}">Daftar gratis</a></div>
    </div>
    <script src="{{ asset('js/ui.js') }}"></script>
</x-layouts.auth>
