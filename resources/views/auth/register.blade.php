@php
    $cats = [
        'produk_pertanian' => ['Produk Pertanian', 'Bibit unggul bersertifikat, pupuk organik, media tanam.', 'leaf'],
        'hasil_pertanian' => ['Hasil Pertanian', 'Sayuran segar panen hari ini, buah lokal, umbi-umbian.', 'sprout'],
        'produk_olahan' => ['Produk Olahan', 'Wedang herbal, selai buah, keripik hasil bumi, olahan rosella.', 'archive'],
        'alat_pertanian' => ['Alat Pertanian', 'Sprayer elektrik, cangkul baja, instalasi pipa hidroponik.', 'settings'],
    ];
    $step = $errors->any() ? (collect(['name','email','password'])->contains(fn ($k) => $errors->has($k)) ? 1 : (collect(['agree'])->contains(fn ($k) => $errors->has($k)) ? 3 : 2)) : 1;
@endphp
<x-layouts.auth title="Daftar Penjual — Bolo Tani" :lokal="false">
    <div class="r-wrap">
        <span class="r-pill">PENDAFTARAN MITRA AGRIBISNIS RESMI</span>
        <h1 class="r-title">Buka Lapak Usaha<br>Agribisnis Anda di <span>Bolo Tani</span></h1>
        <p class="r-sub">Gratis, tanpa potongan komisi. Jangkau pembeli di seluruh Indonesia dan terhubung langsung lewat WhatsApp, tanpa perantara.</p>

        <div class="r-steps" id="steps">
            <div class="r-step" data-pill="1"><i>1</i><span><small>LANGKAH 1</small><b>Akun &amp; Penanggung Jawab</b></span></div>
            <div class="r-step" data-pill="2"><i>2</i><span><small>LANGKAH 2</small><b>Profil Usaha &amp; Lokasi</b></span></div>
            <div class="r-step" data-pill="3"><i>3</i><span><small>LANGKAH 3</small><b>Pakta &amp; Aktivasi</b></span></div>
        </div>

        <form class="r-card" id="regform" method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf
            @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif

            {{-- Langkah 1 --}}
            <section data-step="1">
                <div class="r-head">
                    <div class="ico"><x-icon name="user" :size="17" /></div>
                    <div><h2>Data akun &amp; penanggung jawab</h2><p>Data pengelola untuk kontak resmi mitra.</p></div>
                    <span class="tag"><x-icon name="lock" :size="10" />Data terenkripsi</span>
                </div>
                <div class="r-field">
                    <label class="f-label" for="name">Nama lengkap penanggung jawab / pemilik <i>*</i></label>
                    <div class="f-wrap"><x-icon name="user" :size="14" /><input class="f-input has-ic" id="name" name="name" value="{{ old('name') }}" placeholder="Ahmad Syaifullah" autocomplete="name"></div>
                    <span class="f-hint">Isi sesuai nama pada KTP.</span>
                </div>
                <div class="r-field">
                    <label class="f-label" for="email">Email <i>*</i></label>
                    <input class="f-input" style="height:41px" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email">
                </div>
                <div class="r-two">
                    <div>
                        <label class="f-label" for="password">Kata sandi <i>*</i></label>
                        <div class="f-wrap">
                            <x-icon name="lock" :size="14" />
                            <input class="f-input has-ic" id="password" name="password" type="password" autocomplete="new-password">
                            <button type="button" class="eye" data-eye="password" aria-label="Tampilkan"><span class="on"><x-icon name="eye" :size="15" /></span><span class="off" style="display:none"><x-icon name="eye-off" :size="15" /></span></button>
                        </div>
                    </div>
                    <div>
                        <label class="f-label" for="password_confirmation">Konfirmasi kata sandi <i>*</i></label>
                        <div class="f-wrap">
                            <x-icon name="lock" :size="14" />
                            <input class="f-input has-ic" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                            <button type="button" class="eye" data-eye="password_confirmation" aria-label="Tampilkan"><span class="on"><x-icon name="eye" :size="15" /></span><span class="off" style="display:none"><x-icon name="eye-off" :size="15" /></span></button>
                        </div>
                    </div>
                </div>
                <div class="r-two" style="margin-top:0">
                    <div>
                        <div class="strength"><span class="bars"><i></i><i></i><i></i><i></i></span><b id="strengthLabel"></b></div>
                        <span class="f-hint">Minimal 8 karakter, kombinasi huruf dan angka.</span>
                    </div>
                    <div><div class="match" id="matchLabel" style="display:none"><x-icon name="check" :size="11" />Kata sandi cocok</div></div>
                </div>
                <div class="r-actions" style="margin-top:34px"><button type="button" class="r-btn" data-next>Lanjut: Profil Usaha <x-icon name="arrow-right" :size="13" /></button></div>
            </section>

            {{-- Langkah 2 --}}
            <section data-step="2">
                <div class="r-head">
                    <div class="ico"><x-icon name="home" :size="17" /></div>
                    <div><h2>Profil usaha &amp; titik lokasi kebun</h2><p>Informasi ini tampil di katalog publik dan peta pembeli.</p></div>
                    <span class="tag"><x-icon name="pin" :size="10" />Koordinat GPS</span>
                </div>
                <div class="r-field">
                    <label class="f-label" for="business_name">Nama usaha / toko / kelompok tani <i>*</i></label>
                    <div class="f-wrap"><x-icon name="building" :size="14" /><input class="f-input has-ic" id="business_name" name="business_name" value="{{ old('business_name') }}" placeholder="UD Barokah Tani Organik"></div>
                    <span class="f-hint">Nama ini tampil sebagai merek lapak Anda di katalog Bolo Tani.</span>
                </div>
                <div class="r-field">
                    <label class="f-label">Kategori fokus penjualan <i>*</i> <span style="font-weight:400;text-transform:none;color:var(--a-hint)">· pilih minimal 1</span></label>
                    <div class="cats">
                        @foreach ($cats as $key => [$label, $desc, $icon])
                            <label class="cat">
                                <input type="checkbox" name="categories[]" value="{{ $key }}" @checked(in_array($key, old('categories', ['produk_pertanian'])))>
                                <span class="cico"><x-icon :name="$icon" :size="15" /></span>
                                <span><b>{{ $label }}</b><span>{{ $desc }}</span></span>
                                <span class="box"><x-icon name="check" :size="11" :stroke="3" /></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="r-field">
                    <label class="f-label" for="whatsapp">Nomor WhatsApp pemesanan <i>*</i></label>
                    <div class="wa-field"><span class="pre"><x-icon name="chat" :size="13" />+62</span><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="812 3456 7890" inputmode="tel"></div>
                </div>
                <div class="r-field">
                    <label class="f-label" for="business_email">Email</label>
                    <input class="f-input" type="email" id="business_email" name="business_email" value="{{ old('business_email') }}">
                </div>
                <div class="r-field">
                    <label class="f-label" for="address">Alamat lengkap kebun / lapak <i>*</i></label>
                    <div class="f-wrap"><x-icon name="pin" :size="14" style="top:20px;transform:none" /><textarea class="f-area" id="address" name="address" rows="2" placeholder="Jl. Raya Jember KM 13, Dusun Krajan, RT 02/RW 01 (dekat Kampus Poliwangi)">{{ old('address') }}</textarea></div>
                </div>
                <div class="r-field">
                    <label class="f-label">Titik koordinat lapak <i>*</i></label>
                    <span class="f-hint" style="margin:3px 0 8px;display:block">Isi latitude dan longitude lokasi kebun. Cara cepat: di Google Maps, tekan lama titik lokasi lalu salin angka koordinatnya.</span>
                    <div class="r-two" style="margin-top:0">
                        <div>
                            <label class="f-label" for="lat">Latitude</label>
                            <input class="f-input" style="height:41px" id="lat" name="lat" inputmode="decimal" value="{{ old('lat') }}" placeholder="-8.2192000" autocomplete="off">
                        </div>
                        <div>
                            <label class="f-label" for="lng">Longitude</label>
                            <input class="f-input" style="height:41px" id="lng" name="lng" inputmode="decimal" value="{{ old('lng') }}" placeholder="114.3267000" autocomplete="off">
                        </div>
                    </div>
                    <input type="hidden" name="kecamatan" id="kecamatan" value="{{ old('kecamatan') }}">
                    <input type="hidden" name="desa" id="desa" value="{{ old('desa') }}">
                </div>
                <div class="r-actions" style="border-top:1px solid #eef0f2;padding-top:20px;margin-top:26px;justify-content:space-between">
                    <button type="button" class="r-btn o" data-prev><x-icon name="arrow-left" :size="13" />Kembali</button>
                    <button type="button" class="r-btn" data-next>Lanjut: Pakta &amp; Aktivasi <x-icon name="arrow-right" :size="13" /></button>
                </div>
            </section>

            {{-- Langkah 3 --}}
            <section data-step="3">
                <div class="r-head">
                    <div class="ico"><x-icon name="shield" :size="17" /></div>
                    <div><h2>Fakta integritas &amp; konfirmasi</h2><p>Komitmen kejujuran bobot timbangan, mutu produk segar, dan kesepakatan mitra agribisnis.</p></div>
                </div>
                <div class="sum-title">Periksa data Anda</div>
                <div class="sum">
                    <div><small>Penanggung jawab</small><b data-sum="name">-</b></div>
                    <div><small>WhatsApp</small><b data-sum="wa">-</b></div>
                    <div style="border-top:1px solid #eef0f2;grid-column:1/-1;margin:-2px 0 0"></div>
                    <div><small>Nama usaha</small><b data-sum="biz">-</b></div>
                    <div><small>Lokasi</small><b data-sum="loc">-</b></div>
                    <div class="full"><small>Kategori</small><b data-sum="cats">-</b></div>
                </div>
                <div class="integ">
                    <h4><x-icon name="shield" :size="11" />FAKTA INTEGRITAS PEDAGANG &amp; KOMITMEN MITRA</h4>
                    <p>“Dengan ini saya menyatakan bahwa seluruh data yang diisikan adalah sah dan benar. Saya berkomitmen memastikan mutu komoditas tani tetap segar, transparan dalam berat timbangan, serta mematuhi Kode Etik Mitra Bolo Tani dalam seluruh interaksi dan transaksi dengan pembeli tanpa memberlakukan biaya siluman.”</p>
                    <ul>
                        <li><x-icon name="check" :size="10" :stroke="2.6" />Timbangan pas &amp; akurat</li>
                        <li><x-icon name="check" :size="10" :stroke="2.6" />Higienis &amp; mutu terjaga</li>
                        <li><x-icon name="check" :size="10" :stroke="2.6" />Responsif di WhatsApp</li>
                    </ul>
                </div>
                <label class="agree">
                    <input type="checkbox" name="agree" value="1" @checked(old('agree'))>
                    <span>Saya telah membaca dan menyetujui <a href="#">Syarat &amp; Ketentuan Layanan</a>, <a href="#">Kebijakan Privasi Data</a>, serta <a href="#">Kode Etik Pedagang Mitra Bolo Tani</a>.</span>
                </label>
                <div class="r-actions" style="justify-content:space-between;margin-top:0">
                    <button type="button" class="r-btn o" data-prev><x-icon name="arrow-left" :size="13" />Kembali</button>
                    <button type="submit" class="r-btn">Daftar &amp; Buka Lapak <x-icon name="arrow-right" :size="13" /></button>
                </div>
            </section>

            <p class="r-already {{ '' }}" style="text-align:left">Sudah pernah mendaftar? <a class="link-g" href="{{ route('login') }}">Masuk ke Portal Penjual</a></p>
        </form>
    </div>

    <script src="{{ asset('js/coordinate-input.js') }}"></script>
    <script src="{{ asset('js/ui.js') }}"></script>
    <script>
        (function () {
            var form = document.getElementById('regform');
            var steps = form.querySelectorAll('[data-step]');
            var pills = document.querySelectorAll('[data-pill]');
            var current = {{ $step }};
            var $ = function (id) { return document.getElementById(id); };

            function show(n) {
                current = n;
                steps.forEach(function (s) { s.classList.toggle('show', +s.getAttribute('data-step') === n); });
                pills.forEach(function (p) {
                    var i = +p.getAttribute('data-pill');
                    p.classList.toggle('on', i === n);
                    p.classList.toggle('done', i < n);
                    var small = p.querySelector('small');
                    small.textContent = 'LANGKAH ' + i + (i < n ? ' · SELESAI' : '');
                    p.querySelector('i').innerHTML = i < n ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>' : i;
                });
                if (n === 3) summary();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function fail(el, msg) {
                var old = el.parentNode.querySelector('.f-err.js'); if (old) old.remove();
                var s = document.createElement('span'); s.className = 'f-err js'; s.textContent = msg;
                (el.closest('.f-wrap, .wa-field, .cats') || el).insertAdjacentElement('afterend', s);
                el.focus && el.focus();
                return false;
            }
            function clean() { form.querySelectorAll('.f-err.js').forEach(function (e) { e.remove(); }); }

            function valid(n) {
                clean();
                if (n === 1) {
                    if (!$('name').value.trim()) return fail($('name'), 'Nama penanggung jawab wajib diisi.');
                    if (!/^\S+@\S+\.\S+$/.test($('email').value)) return fail($('email'), 'Masukkan alamat email yang valid.');
                    var pw = $('password').value;
                    if (pw.length < 8 || !/[A-Za-z]/.test(pw) || !/\d/.test(pw)) return fail($('password'), 'Kata sandi minimal 8 karakter, kombinasi huruf dan angka.');
                    if (pw !== $('password_confirmation').value) return fail($('password_confirmation'), 'Konfirmasi kata sandi tidak cocok.');
                }
                if (n === 2) {
                    if (!$('business_name').value.trim()) return fail($('business_name'), 'Nama usaha wajib diisi.');
                    if (!form.querySelector('[name="categories[]"]:checked')) return fail(form.querySelector('.cats'), 'Pilih minimal satu kategori.');
                    if (!/^\d[\d\s-]{7,}$/.test($('whatsapp').value.trim())) return fail($('whatsapp'), 'Nomor WhatsApp tidak valid.');
                    if (!$('address').value.trim()) return fail($('address'), 'Alamat lengkap wajib diisi.');
                    var la = parseFloat($('lat').value.replace(',', '.')), lo = parseFloat($('lng').value.replace(',', '.'));
                    if (!isFinite(la) || Math.abs(la) > 90) return fail($('lat'), 'Latitude tidak valid (contoh: -8.2192).');
                    if (!isFinite(lo) || Math.abs(lo) > 180) return fail($('lng'), 'Longitude tidak valid (contoh: 114.3267).');
                }
                return true;
            }

            function summary() {
                var q = function (k) { return form.querySelector('[data-sum="' + k + '"]'); };
                var cats = Array.prototype.map.call(form.querySelectorAll('[name="categories[]"]:checked'), function (c) { return c.closest('.cat').querySelector('b').textContent; });
                q('name').textContent = $('name').value || '-';
                q('wa').textContent = '+62 ' + ($('whatsapp').value || '-');
                q('biz').textContent = $('business_name').value || '-';
                q('loc').textContent = $('address').value || '-';
                q('cats').textContent = cats.join(', ') || '-';
            }

            form.querySelectorAll('[data-next]').forEach(function (b) { b.addEventListener('click', function () { if (valid(current)) show(current + 1); }); });
            form.querySelectorAll('[data-prev]').forEach(function (b) { b.addEventListener('click', function () { show(current - 1); }); });

            // kekuatan kata sandi
            var bars = form.querySelectorAll('.strength .bars i');
            function strength() {
                var v = $('password').value, score = 0;
                if (v.length >= 8) score++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if (/\d/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) score++;
                bars.forEach(function (b, i) { b.classList.toggle('on', i < score); });
                $('strengthLabel').textContent = v ? ['Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat kuat'][score] : '';
                var same = v && v === $('password_confirmation').value;
                $('matchLabel').style.display = same ? 'flex' : 'none';
            }
            $('password').addEventListener('input', strength);
            $('password_confirmation').addEventListener('input', strength);

            initCoordinateInput({ lat: 'lat', lng: 'lng', kecamatan: 'kecamatan', desa: 'desa' });

            strength();
            show(current);
        })();
    </script>
</x-layouts.auth>
