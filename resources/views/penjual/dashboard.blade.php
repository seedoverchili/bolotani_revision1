<x-layouts.dash title="Ringkasan Usaha — Bolo Tani" role="penjual" active="ringkasan">
    @if (session('ok'))<div class="d-alert ok"><x-icon name="circle-check" :size="16" /><span>{{ session('ok') }}</span></div>@endif
    @if ($errors->any())<div class="d-alert bad"><x-icon name="warning" :size="16" /><span>{{ $errors->first() }}</span></div>@endif

    <section class="t-banner">
        <div class="logo">
            @if ($tenant->avatarUrl())<img src="{{ $tenant->avatarUrl() }}" alt="">@else{{ $tenant->initials() }}@endif
        </div>
        <div>
            <span class="t-chip"><i></i>Toko Terdaftar Bolo Tani • Poliwangi Agro</span>
            <h1>Selamat Datang, {{ $tenant->name }}!</h1>
            <p>Kelola katalog komoditas, pesanan WhatsApp, dan lokasi kebun Anda.</p>
        </div>
        <div class="btns" x-data>
            <a class="t-btn-w" href="{{ route('pedagang.show', $tenant->slug) }}" target="_blank"><x-icon name="eye" :size="13" />Lihat Toko Publik</a>
            <button type="button" class="t-btn-o" id="shareBtn" data-url="{{ route('pedagang.show', $tenant->slug) }}"><x-icon name="share" :size="13" /><span>Bagikan Lapak</span></button>
        </div>
    </section>

    <section class="t-stats">
        <div class="t-stat">
            <div class="row">TOTAL PRODUK AKTIF <span class="ico"><x-icon name="cube" :size="13" /></span></div>
            <div class="big"><b>{{ $products->count() }}</b><small>Komoditas Tani</small></div>
            <div class="sub"><span class="ready">● {{ $ready }} Ready</span><span style="color:#d0d5dd">|</span><span class="out">● {{ $out }} Habis</span></div>
        </div>
        <div class="t-stat">
            <div class="row">KLIK WHATSAPP (WA.ME) <span class="ico"><x-icon name="chat" :size="13" /></span></div>
            <div class="big"><b>{{ number_format($tenant->wa_clicks, 0, ',', '.') }}</b></div>
            <p class="note">Total klik tombol WhatsApp pembeli sejak lapak aktif.</p>
        </div>
        <div class="t-stat">
            <div class="row">STATUS INTEGRASI GPS <span class="ico" style="background:#e8f3ff;color:#2563eb"><x-icon name="warning" :size="13" /></span></div>
            <span class="gps"><i></i>{{ $tenant->lat ? 'Terhubung Aktif' : 'Belum Diatur' }}</span>
            <p class="note" style="margin-top:12px">{{ $tenant->areaLabel() ?: $tenant->address }}, Banyuwangi.</p>
        </div>
    </section>

    <a class="t-pin-btn" href="#profil"><x-icon name="map" :size="13" />Profil &amp; Titik Lokasi Peta</a>

    <section class="d-card t-prof" id="profil" style="margin-top:18px">
        <form method="POST" action="{{ route('penjual.profil.update') }}">
            @csrf @method('PUT')
            <div class="head">
                <div>
                    <h2>Profil &amp; Identitas Usaha</h2>
                    <p class="hint">Data ini ditampilkan pada kartu produk, katalog agro-tile, serta sertifikat jaminan bibit Bolo Tani.</p>
                </div>
                <span class="ic"><x-icon name="package" :size="16" /></span>
            </div>

            <label class="l">Foto Sampul &amp; Logo Lapak Kebun</label>
            <div class="t-cover">
                <img src="{{ $tenant->coverUrl() }}" alt="" style="object-position:50% {{ $tenant->cover_pos }}%">
                <span class="av">@if ($tenant->avatarUrl())<img src="{{ $tenant->avatarUrl() }}" alt="">@endif</span>
            </div>
            <div class="t-edit"><a href="{{ route('penjual.foto') }}"><x-icon name="pencil" :size="11" />Ubah Logo Avatar (.jpg, .png maks. 2 MB)</a></div>

            <label class="l" for="name">Nama Usaha / Kelompok Tani (Tenant)</label>
            <div class="d-field"><x-icon name="building" :size="14" /><input class="d-in pad" id="name" name="name" value="{{ old('name', $tenant->name) }}" required></div>

            <label class="l" for="description">Deskripsi Toko &amp; Spesialisasi Komoditas</label>
            <textarea class="d-ta" id="description" name="description" rows="3">{{ old('description', $tenant->description) }}</textarea>

            <label class="l" for="main_category">Kategori Utama</label>
            <div class="d-field"><x-icon name="tag" :size="14" /><input class="d-in pad" id="main_category" name="main_category" value="{{ old('main_category', $tenant->main_category) }}"></div>

            <label class="l" for="address">Alamat Lengkap Lapak</label>
            <textarea class="d-ta" id="address" name="address" rows="2" required>{{ old('address', $tenant->address) }}</textarea>

            <label class="l">Titik Koordinat Lapak</label>
            <p class="hint" style="margin:6px 0 8px">Isi latitude dan longitude lokasi lapak. Di Google Maps, tekan lama titik lokasi lalu salin angkanya. Titik ini dipakai di peta halaman produk dan tombol rute.</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="d-field"><x-icon name="pin" :size="14" /><input class="d-in pad" id="lat" name="lat" inputmode="decimal" placeholder="Latitude, mis. -8.2192" value="{{ old('lat', $tenant->lat) }}" autocomplete="off" required></div>
                <div class="d-field"><x-icon name="pin" :size="14" /><input class="d-in pad" id="lng" name="lng" inputmode="decimal" placeholder="Longitude, mis. 114.3267" value="{{ old('lng', $tenant->lng) }}" autocomplete="off" required></div>
            </div>
            <input type="hidden" name="kecamatan" id="kecamatan" value="{{ old('kecamatan', $tenant->kecamatan) }}">
            <input type="hidden" name="desa" id="desa" value="{{ old('desa', $tenant->desa) }}">

            <button type="submit" class="d-btn" style="margin-top:20px"><x-icon name="archive" :size="13" />Simpan Profil Lapak</button>
        </form>
    </section>

    <section class="d-card t-cat" id="katalog">
        <div class="head">
            <div>
                <h2>Katalog Cepat &amp; Status Ketersediaan</h2>
                <p class="hint">Ubah status stok produk tanpa membuka editor penuh.</p>
            </div>
            <a class="d-btn" href="{{ route('penjual.produk.create') }}"><x-icon name="plus" :size="13" />Tambah Produk Baru</a>
        </div>
        <table class="t-table">
            <thead><tr><th>KOMODITAS / BIBIT</th><th>HARGA SATUAN</th><th>KETERSEDIAAN</th><th>AKSI</th></tr></thead>
            <tbody>
            @forelse ($products as $product)
                <tr data-filter-row>
                    <td>
                        <div class="prod">
                            <img src="{{ $product->coverUrl() }}" alt="">
                            <span>
                                <b>{{ $product->name }}</b>
                                <small>SKU: {{ $product->sku }}</small>
                                @unless ($product->isApproved())<em style="color:#b45309;font-style:normal;font-weight:700">Menunggu persetujuan admin</em>@endunless
                                @if ($product->images->count() > 1)<em>{{ $product->images->count() }} Foto</em>@endif
                            </span>
                        </div>
                    </td>
                    <td class="price"><b>{{ $product->priceFormatted() }}</b><small>/{{ \Illuminate\Support\Str::before($product->unit, ' ') }}</small></td>
                    <td>
                        <form method="POST" action="{{ route('penjual.produk.stok', $product) }}" data-confirm="Ubah status {{ $product->name }} menjadi {{ $product->stock > 0 ? 'Habis' : 'Ready' }}?" data-confirm-title="Ubah ketersediaan?" data-confirm-ok="Ya, Ubah" data-confirm-tone="ok">@csrf @method('PATCH')
                            <button type="submit" class="stock-btn {{ $product->stock > 0 ? 'ready' : 'out' }}" title="Ubah status stok">● {{ $product->stock > 0 ? 'Ready' : 'Habis' }}</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('penjual.produk.destroy', $product) }}" data-confirm="{{ $product->name }} akan dihapus permanen dari katalog dan marketplace." data-confirm-title="Hapus produk?" data-confirm-ok="Ya, Hapus" data-confirm-tone="danger">@csrf @method('DELETE')
                            <button type="submit" class="del-btn"><x-icon name="trash" :size="12" />Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;padding:36px;font:500 12px/18px var(--pj);color:#667085">Belum ada produk. Tambahkan komoditas pertama Anda.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <script>
        document.getElementById('shareBtn').addEventListener('click', function () {
            var btn = this, url = btn.getAttribute('data-url');
            if (navigator.share) { navigator.share({ title: document.title, url: url }).catch(function () {}); return; }
            navigator.clipboard.writeText(url).then(function () {
                var s = btn.querySelector('span'), t = s.textContent; s.textContent = 'Tautan Tersalin';
                setTimeout(function () { s.textContent = t; }, 1800);
            });
        });
    </script>
    <script src="{{ asset('js/coordinate-input.js') }}"></script>
    <script>initCoordinateInput({ lat: 'lat', lng: 'lng', kecamatan: 'kecamatan', desa: 'desa' });</script>
</x-layouts.dash>
