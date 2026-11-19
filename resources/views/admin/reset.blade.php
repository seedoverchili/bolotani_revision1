<x-layouts.auth title="Atur Ulang Kata Sandi — Bolo Tani" :plain="true">
    <div class="ac-card">
        @include('admin.partials.logo')
        <h1>Atur Ulang Kata Sandi</h1>
        <p class="lead">Buat kata sandi baru untuk akun Super Admin Anda.</p>

        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif
        {{-- // 7.29 — Bagian 7 · Pemulihan sandi — Tampilkan kesalahan pada formulir. Fungsi: Menampilkan kesalahan token, kata sandi, atau konfirmasi dari server. --}}

        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
        {{-- // 7.17 — Bagian 7 · Pemulihan sandi — Sertakan token dan email. Fungsi: Meneruskan token dan email yang nantinya dicocokkan oleh broker Laravel. --}}
            <div class="r-field">
                <label class="f-label" for="password">Kata Sandi Baru</label>
                <div class="f-wrap">
                    <x-icon name="lock" :size="14" />
                    <input class="f-input has-ic" id="password" name="password" type="password" placeholder="Minimal 8 karakter kombinasi" required autocomplete="new-password">
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
            <div class="r-field" style="margin-bottom:0">
                <label class="f-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <div class="f-wrap">
                    <x-icon name="lock" :size="14" />
                    <input class="f-input has-ic" id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi kata sandi baru" required autocomplete="new-password">
                    <button type="button" class="eye" data-eye="password_confirmation" aria-label="Tampilkan">
                        <span class="on">
                            <x-icon name="eye" :size="15" />
                        </span>
                        <span class="off" style="display:none">
                            <x-icon name="eye-off" :size="15" />
                        </span>
                    </button>
                </div>
            </div>
            <div class="req" id="req">
                <h5>PERSYARATAN KATA SANDI:</h5>
                <div data-r="len"><i></i>Minimal 8 karakter</div>
                <div data-r="case"><i></i>Mengandung huruf besar &amp; huruf kecil</div>
                <div data-r="sym"><i></i>Mengandung angka &amp; simbol khusus</div>
            </div>
            <button type="submit" class="btn-main dark" style="margin-top:0">Simpan Kata Sandi Baru</button>
        </form>
        {{-- // 7.19 — Bagian 7 · Pemulihan sandi — Kirim sandi dan konfirmasi. Fungsi: Mengirim sandi dan konfirmasi. Daftar syarat membantu admin memeriksa isian sebelum menyimpan. --}}
        <a class="ac-back" href="{{ route('admin.login') }}"><x-icon name="arrow-left" :size="11" />Kembali ke Login</a>
    </div>
    <script src="{{ asset('js/ui.js') }}"></script>
    <script>
        (function () {
            var pw = document.getElementById('password');
            var rules = {
                len: function (v) { return v.length >= 8; },
                'case': function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v); },
                sym: function (v) { return /\d/.test(v) && /[^A-Za-z0-9]/.test(v); }
            };
            pw.addEventListener('input', function () {
                Object.keys(rules).forEach(function (k) {
                    document.querySelector('[data-r="' + k + '"]').classList.toggle('ok', rules[k](pw.value));
                });
            });
        })();
        // 7.18 — Bagian 7 · Pemulihan sandi — Tunjukkan syarat sandi saat mengetik. Fungsi: Memperbarui indikator persyaratan saat pengguna mengetik. Validasi akhir berjalan di controller.
    </script>
</x-layouts.auth>
