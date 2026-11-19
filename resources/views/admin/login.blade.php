<x-layouts.auth title="Masuk Admin — Bolo Tani" :plain="true">
    <div class="ac-card">
        @include('admin.partials.logo')
        {{-- // 2.7 — Bagian 2 · Masuk admin — Panggil komponen logo. Fungsi: Memuat logo bersama di atas formulir masuk. --}}
        <h1>Masuk Admin</h1>
        <p class="lead">Masukkan email dan kata sandi Anda untuk mengakses dashboard.</p>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif
        {{-- // 2.13 — Bagian 2 · Masuk admin — Tampilkan kesalahan login. Fungsi: Menampilkan kesalahan masuk atau pesan sesi dari tindakan sebelumnya. --}}

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <div class="r-field">
                <label class="f-label" for="email">Email Administrator</label>
                <div class="f-wrap">
                    <x-icon name="user" :size="14" />
                    <input class="f-input has-ic" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>
            </div>
            <div class="r-field" style="margin-bottom:0">
                <label class="f-label" for="password"><span>Kata Sandi</span><a href="{{ route('admin.password.request') }}">Lupa kata sandi?</a></label>
                <div class="f-wrap">
                    <x-icon name="lock" :size="14" />
                    <input class="f-input has-ic" id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="button" class="eye" data-eye="password" aria-label="Tampilkan">
                        <span class="on">
                            <x-icon name="eye" :size="15" />
                        </span>
                        <span class="off" style="display:none">
                            <x-icon name="eye-off" :size="15" />
                        </span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-main dark">Masuk ke Dashboard <x-icon name="arrow-right" :size="13" /></button>
        </form>
        {{-- // 2.10 — Bagian 2 · Masuk admin — Kirim email dan sandi. Fungsi: Mengirim email dan sandi lewat POST dengan token CSRF. Tombol mata mengubah tampilan isian sandi. --}}
    </div>
    <script src="{{ asset('js/ui.js') }}"></script>
    {{-- // 2.9 — Bagian 2 · Masuk admin — Aktifkan tombol tampilkan sandi. Fungsi: Mengaktifkan tombol tampil atau sembunyikan sandi memakai JavaScript bawaan. --}}
</x-layouts.auth>
