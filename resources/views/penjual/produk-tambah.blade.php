@php
    $pillars = [
        'produk_pertanian' => ['Produk Pertanian', 'Bibit, benih bersertifikat, media tanam, dan pupuk alami.', 'leaf'],
        'hasil_pertanian' => ['Hasil Pertanian', 'Panen segar, jamur, sayur, dan buah lokal.', 'book'],
        'produk_olahan' => ['Produk Olahan', 'Minuman herbal, selai jahe, cokelat rempah, dan sari buah.', 'archive'],
        'alat_pertanian' => ['Alat Pertanian', 'Sprayer, chopper pakan, cangkul, dan selang irigasi.', 'settings'],
    ];
    $pillars['hasil_pertanian'][2] = 'sprout';
@endphp
<x-layouts.dash title="Tambah Komoditas — Bolo Tani" role="penjual" active="produk">
    <div class="crumb-s"><x-icon name="archive" :size="11" /><a href="{{ route('penjual.dashboard') }}">Dashboard Tenant</a><span>›</span><b>Tambah Komoditas Baru</b></div>
    <span class="pill-v"><x-icon name="circle-check" :size="11" />KATALOG AGRIBISNIS TERVERIFIKASI</span>
    <h1 class="p-title" style="margin-top:0">Tambah Komoditas Agribisnis Baru</h1>
    <p class="p-sub">Isi informasi produk dengan akurat agar calon pembeli mudah menghubungi Anda melalui WhatsApp.</p>

    @if ($errors->any())<div class="d-alert bad" style="margin-top:14px"><x-icon name="warning" :size="16" /><span>{{ $errors->first() }}</span></div>@endif

    <form method="POST" action="{{ route('penjual.produk.store') }}" enctype="multipart/form-data" id="produkForm">
        @csrf
        <section class="sec">
            <div class="h"><span class="n">1</span><div><b>Pilar Kategori Utama Bolo Tani</b><small>Pilih satu atau lebih klasifikasi komoditas agribisnis Anda.</small></div><span class="chip red">Wajib Diisi</span></div>
            <div class="pillars">
                @foreach ($pillars as $key => [$label, $desc, $icon])
                    <label class="pillar">
                        <input type="checkbox" name="categories[]" value="{{ $key }}" @checked(in_array($key, old('categories', ['produk_pertanian'])))>
                        <span class="pi"><x-icon :name="$icon" :size="14" /></span>
                        <span><b>{{ $label }}</b><small>{{ $desc }}</small></span>
                        <span class="rd"><x-icon name="check" :size="11" :stroke="3" /></span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="sec">
            <div class="h"><span class="n">2</span><div><b>Nama Produk &amp; Varietas</b><small>Gunakan nama yang jelas dan mudah dicari pembeli.</small></div></div>
            <div style="margin-bottom:14px">
                <div class="fl"><span>Nama Komoditas / Produk <i style="color:#dc2626;font-style:normal">*</i></span><span class="c"><span id="nameCount">0</span> / 100 karakter</span></div>
                <input class="d-in" name="name" id="pname" maxlength="100" value="{{ old('name') }}" required style="background:#fff;border-color:#e0e6ee">
                <div class="fh"><x-icon name="info" :size="11" />Sertakan varietas dan sertifikasi resmi produk.</div>
            </div>
            <div>
                <div class="fl"><span>Sub-Kategori / Kata Kunci Tag</span></div>
                <input class="d-in" name="tags" value="{{ old('tags') }}" placeholder="Bibit Buah Unggul, Sambung Pucuk, Sertifikasi BPSB" style="background:#fff;border-color:#e0e6ee">
            </div>
        </section>

        <section class="sec">
            <div class="h"><span class="n">3</span><div><b>Foto Komoditas (Maks. 5 Foto)</b><small>Tampilkan foto asli kebun atau produk Anda.</small></div><span class="chip grn" id="photoChip">0 / 5 Terisi</span></div>
            <div class="tipbox"><x-icon name="circle-check" :size="13" /><span><b>Tips Foto Berkualitas:</b> Gunakan pencahayaan alami dan kondisi serta mutu produk terlihat jelas.</span></div>
            <div class="ph-grid" id="photoGrid">
                <label class="ph-add" id="addTile"><span class="ci"><x-icon name="upload" :size="13" /></span>Unggah Foto<input type="file" id="photoInput" accept="image/png,image/jpeg,image/webp" multiple hidden></label>
                <label class="ph-add second" id="addTile2" for="photoInput"><span class="ci"><x-icon name="plus" :size="13" /></span>Tambah Foto</label>
            </div>
            <input type="file" name="photos[]" id="photoReal" multiple hidden>
            <p class="ph-note">Format JPG, PNG, atau WEBP. Maksimal 5 MB per foto.</p>
        </section>

        <section class="sec">
            <div class="h"><span class="n">4</span><div><b>Penetapan Harga &amp; Satuan Jual</b><small>Tentukan harga yang kompetitif dan transparan.</small></div></div>
            <div class="two">
                <div>
                    <div class="fl"><span>Harga Jual Petani (Rp) <i style="color:#dc2626;font-style:normal">*</i></span></div>
                    <div class="rp"><span>Rp</span><input class="d-in" name="price" id="price" inputmode="numeric" value="{{ old('price') }}" required style="background:#fff;border-color:#e0e6ee"></div>
                    <div class="fh">Harga bersih di tingkat tenant.</div>
                </div>
                <div>
                    <div class="fl"><span>Satuan Jual <i style="color:#dc2626;font-style:normal">*</i></span></div>
                    <select class="unit-select" name="unit" id="unit" style="width:100%">
                        @foreach ($units as $u)<option value="{{ $u }}" @selected(old('unit', 'buah') === $u)>{{ $u }}</option>@endforeach
                        @if (old('unit') && ! in_array(old('unit'), $units))<option value="{{ old('unit') }}" selected>{{ old('unit') }}</option>@endif
                    </select>
                    <div class="fh">Pilih satuan, atau ketik lalu tekan Enter untuk menambah satuan baru.</div>
                </div>
            </div>
        </section>

        <section class="sec">
            <div class="h"><span class="n">5</span><div><b>Deskripsi &amp; Keunggulan Komoditas</b><small>Jelaskan spesifikasi, sertifikasi, asal bibit, dan saran perawatan.</small></div></div>
            <div class="editor">
                <div class="tb">
                    <button type="button" data-cmd="bold" title="Tebal"><x-icon name="bold" :size="13" /></button>
                    <button type="button" data-cmd="italic" title="Miring"><x-icon name="italic" :size="13" /></button>
                    <button type="button" data-cmd="insertUnorderedList" title="Daftar"><x-icon name="list" :size="13" /></button>
                    <button type="button" data-cmd="insertOrderedList" title="Daftar angka"><x-icon name="list-num" :size="13" /></button>
                </div>
                <div class="body" id="editor" contenteditable="true">{!! old('description') !!}</div>
            </div>
            <textarea name="description" id="descField" hidden></textarea>
        </section>

        @if (session('saved'))
            <div class="okbar"><span class="ci"><x-icon name="check" :size="12" :stroke="3" /></span><div><b>Produk Berhasil Dikirim!</b><small>Produk akan tayang di marketplace setelah disetujui admin.</small></div></div>
        @endif

        <div class="foot-act">
            <a class="d-btn o" href="{{ route('penjual.dashboard') }}#katalog">Kembali ke Katalog</a>
            <button type="submit" class="d-btn" data-confirm="Produk akan dikirim ke admin dan tayang di marketplace setelah disetujui." data-confirm-title="Kirim produk?" data-confirm-ok="Ya, Kirim" data-confirm-tone="ok"><x-icon name="check" :size="13" :stroke="2.6" />Simpan &amp; Ajukan Produk</button>
        </div>
    </form>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        jQuery(function ($) {
            $('#unit').select2({
                tags: true,
                width: '100%',
                placeholder: 'Pilih atau tambah satuan',
                language: {
                    noResults: function () { return 'Satuan tidak ditemukan'; },
                    searching: function () { return 'Mencari...'; }
                },
                createTag: function (params) {
                    var term = $.trim(params.term).toLowerCase();
                    if (term === '' || term.length > 40 || !/^[a-z0-9\s\/().,-]+$/i.test(term)) return null;
                    var exists = false;
                    $('#unit option').each(function () { if ($(this).text().toLowerCase() === term) exists = true; });
                    return exists ? null : { id: term, text: term, isNew: true };
                },
                templateResult: function (d) {
                    return d.isNew ? $('<span>+ Tambah satuan "' + $('<i>').text(d.text).html() + '"</span>') : d.text;
                }
            });
        });
    </script>
    <script>
        (function () {
            var MAX = 5, files = [];
            var grid = document.getElementById('photoGrid'), input = document.getElementById('photoInput'), real = document.getElementById('photoReal');
            var add1 = document.getElementById('addTile'), add2 = document.getElementById('addTile2'), chip = document.getElementById('photoChip');
            var labels = ['Foto Utama', 'Foto 2', 'Foto 3', 'Foto 4', 'Foto 5'];

            function sync() {
                var dt = new DataTransfer(); files.forEach(function (f) { dt.items.add(f); }); real.files = dt.files;
                grid.querySelectorAll('.ph').forEach(function (n) { n.remove(); });
                files.forEach(function (f, i) {
                    var d = document.createElement('div'); d.className = 'ph';
                    var img = document.createElement('img'); img.alt = f.name; img.src = URL.createObjectURL(f);
                    var lb = document.createElement('span'); lb.className = 'lb'; lb.textContent = labels[i];
                    var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'rm'; rm.setAttribute('aria-label', 'Hapus foto');
                    rm.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
                    rm.addEventListener('click', function () { files.splice(i, 1); sync(); });
                    d.appendChild(img); d.appendChild(lb); d.appendChild(rm);
                    grid.insertBefore(d, add1);
                });
                // Awal (belum ada foto): "Unggah Foto" + "Tambah Foto". Setelah ada foto: hanya "Tambah Foto".
                add1.style.display = files.length === 0 ? "" : "none";
                add2.style.display = files.length < MAX ? "" : "none";
                chip.textContent = files.length + ' / ' + MAX + ' Terisi';
            }
            input.addEventListener('change', function () {
                Array.prototype.forEach.call(input.files, function (f) {
                    if (files.length >= MAX) return;
                    if (f.size > 5 * 1024 * 1024) { alert(f.name + ' melebihi 5 MB.'); return; }
                    files.push(f);
                });
                input.value = ''; sync();
            });
            add1.addEventListener('click', function (e) { if (e.target !== input) { e.preventDefault(); input.click(); } });
            sync();

            // harga: tampil dengan pemisah ribuan, kirim angka murni
            var price = document.getElementById('price');
            price.addEventListener('input', function () {
                var digits = price.value.replace(/\D/g, '');
                price.value = digits ? Number(digits).toLocaleString('id-ID') : '';
            });
            var name = document.getElementById('pname'), count = document.getElementById('nameCount');
            var upd = function () { count.textContent = name.value.length; }; name.addEventListener('input', upd); upd();

            // editor
            document.querySelectorAll('[data-cmd]').forEach(function (b) {
                b.addEventListener('mousedown', function (e) { e.preventDefault(); document.execCommand(b.getAttribute('data-cmd')); });
            });
            document.getElementById('produkForm').addEventListener('submit', function () {
                document.getElementById('descField').value = document.getElementById('editor').innerHTML;
                price.value = price.value.replace(/\D/g, '');
            });
        })();
    </script>
</x-layouts.dash>
