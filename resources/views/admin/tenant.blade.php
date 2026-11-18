<x-layouts.dash title="Detail & Edit Pedagang — Bolo Tani Admin" role="admin" active="dashboard">
    <div class="a-bread"><a href="{{ route('admin.dashboard') }}">Dashboard Utama</a> › <b>Detail Pedagang</b></div>
    <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:8px;font:600 11.5px/16px var(--pj);margin-top:8px">
        <x-icon name="arrow-left" :size="13" />Kembali ke Dashboard</a>
    <h1 class="a-h1">Detail &amp; Edit Pedagang</h1>
    <p class="a-sub">Periksa produk serta perbarui profil dan kontak usaha pedagang.</p>

    @if (session('ok'))<div class="d-alert ok" style="margin-top:14px"><x-icon name="circle-check" :size="16" /><span>{{ session('ok') }}</span></div>@endif
    @if ($errors->any())<div class="d-alert bad" style="margin-top:14px"><x-icon name="warning" :size="16" /><span>{{ $errors->first() }}</span></div>@endif
        {{-- // 1.15 — Bagian 1 · Persetujuan produk — Tampilkan pesan hasil. Fungsi: Setelah controller kembali ke detail lapak, halaman menampilkan pesan hasil persetujuan dari sesi. Blok ini juga menampilkan kesalahan validasi profil. --}}

    <form method="POST" action="{{ route('admin.tenant.update', $tenant) }}" id="tenantForm">
        @csrf @method('PUT')
        <section class="d-card" style="margin-top:16px">
            <div class="ad-head">
                <span class="in">{{ $tenant->initials() }}</span>
                <div>
                    <h2>{{ $tenant->name }}</h2>
                    <div style="font:400 9.5px/13px var(--pj);color:#667085;margin-top:2px">ID: {{ $tenant->code }}</div>
                    <div class="m">
                        <span>
                            <x-icon name="pin" :size="11" />{{ $tenant->kecamatan }}, Banyuwangi</span>
                            <span>
                                <x-icon name="calendar" :size="11" />Terdaftar {{ $tenant->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') }}</span>
                            </div>
                </div>
                <div class="rt">
                    <span class="st ok"><x-icon name="circle-check" :size="11" />{{ $tenant->isApproved() ? 'Aktif' : 'Belum aktif' }}</span>
                </div>
            </div>
        </section>
        {{-- // 4.1 — Bagian 4 · Edit lapak — Kenali lapak yang dipilih. Fungsi: Menunjukkan identitas dan status lapak yang sedang diperiksa. --}}

        <div class="ad-two">
            <section class="d-card">
                <div class="ad-sec-h">
                    <span class="ib">
                        <x-icon name="user" :size="14" />
                    </span>
                    <div>
                        <b>Kontak Usaha</b>
                        <small>Informasi pengelola dan kontak.</small>
                    </div>
                </div>
                <div class="ad-field">
                    <label for="owner_name">Nama Pengelola</label>
                    <input class="d-in" id="owner_name" name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}">
                </div>
                <div class="ad-field">
                    <label for="whatsapp">Nomor WhatsApp</label>
                    <input class="d-in" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $tenant->whatsapp) }}">
                </div>
                <div class="ad-field" style="margin-bottom:6px">
                    <label for="email">Email</label>
                    <input class="d-in" id="email" name="email" type="email" value="{{ old('email', $tenant->email) }}">
                </div>
                <span class="f-hint">Email kontak pedagang.</span>
                {{-- // 4.2 — Bagian 4 · Edit lapak — Isi kontak usaha. Fungsi: Menampilkan isian kontak usaha. old() mempertahankan masukan jika validasi gagal. --}}
            </section>
            <section class="d-card">
                <div class="ad-sec-h">
                    <span class="ib">
                        <x-icon name="store" :size="14" />
                    </span>
                    <div>
                        <b>Profil Lapak</b>
                        <small>Informasi usaha yang tampil di marketplace.</small>
                    </div>
                </div>
                <div class="ad-field">
                    <label for="name">Nama Lapak</label>
                    <input class="d-in" id="name" name="name" value="{{ old('name', $tenant->name) }}">
                </div>
                <div class="two" style="gap:12px">
                    <div class="ad-field"><label for="main_category">Kategori Usaha</label>
                        <div class="d-field"><select class="d-sel" id="main_category" name="main_category">
                            @php $cur = old('main_category', $tenant->main_category); @endphp
                            <option value="">Belum ditentukan</option>
                            @foreach ($categories as $label)<option value="{{ $label }}" @selected($cur === $label)>{{ $label }}</option>@endforeach
                            @if ($cur && ! in_array($cur, $categories))<option value="{{ $cur }}" selected>{{ $cur }}</option>@endif
                        </select><x-icon name="chevron-down" :size="14" style="left:auto;right:12px;top:10px" /></div>
                    </div>
                    <div class="ad-field">
                        <label for="kecamatan">Kecamatan</label>
                        <input class="d-in" id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $tenant->kecamatan) }}">
                    </div>
                </div>
                <div class="ad-field" style="margin-bottom:0">
                    <label for="address">Alamat Lapak</label>
                    <textarea class="d-ta" id="address" name="address" rows="2">{{ old('address', $tenant->address) }}</textarea>
                </div>
                {{-- // 4.3 — Bagian 4 · Edit lapak — Perbarui data profil. Fungsi: Mengisi profil lapak dari data tersimpan dan mempertahankan pilihan kategori lama. --}}
            </section>
        </div>

        <section class="d-card" style="margin-top:18px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div class="ad-sec-h" style="margin:0"><span class="ib" style="background:none;color:#167a3c"><x-icon name="cube" :size="15" /></span>
                    <div>
                        <b>Produk Pedagang <span class="cnt">{{ $tenant->products->count() }} produk</span>
                    </b>
                    <small>Daftar produk milik {{ $tenant->name }}.</small>
                    </div>
                </div>
                @if ($tenant->products->isNotEmpty())<div class="p-search">
                <x-icon name="search" :size="12" />
                <input type="search" aria-label="Cari produk pedagang" placeholder="Cari produk pedagang..." data-filter-input>
                </div>@endif
            </div>

            @if ($tenant->products->isEmpty())
                <div class="empty-p">
                    <div class="ci"><x-icon name="cube" :size="46" :stroke="1.2" /></div>
                    <b>Belum Ada Produk</b>
                    <p>Pedagang belum menambahkan produk.</p>
                </div>
            @else
                <table class="a-table" style="margin-top:16px">
                    <thead><tr><th>PRODUK</th><th>KATEGORI</th><th>HARGA</th><th>STOK</th><th>STATUS</th><th>AKSI</th></tr></thead>
                    <tbody>
                    @foreach ($tenant->products as $product)
                        <tr data-filter-row>
                            <td>
                                <div class="tn">
                                    <img src="{{ $product->coverUrl() }}" alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover">
                                    <span>
                                        <b style="font-size:11.5px">{{ $product->name }}</b>
                                        <small style="max-width:none">SKU: {{ $product->sku }}</small>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $product->categoryLabel() }}</td>
                            <td>
                                <b style="font-weight:800">{{ $product->priceFormatted() }}</b> <span style="color:#98a2b3;font-size:9.5px">/ {{ \Illuminate\Support\Str::before($product->unit, ' ') }}</span>
                            </td>
                            <td>{{ $product->stock }} {{ \Illuminate\Support\Str::before($product->unit, ' ') }}</td>
                            <td>@if ($product->isApproved())<span class="tag-ok {{ $product->stock > 0 ? '' : 'tag-out' }}">{{ $product->stock > 0 ? 'Disetujui' : 'Disetujui · Habis' }}</span>@else<span class="tag-ok" style="background:#fef3c7;color:#b45309">Menunggu</span>@endif</td>
                            {{-- // 1.16 — Bagian 1 · Persetujuan produk — Perlihatkan status terbaru. Fungsi: Saat halaman detail dimuat kembali, produk approved berlabel Disetujui atau Disetujui · Habis sesuai stok. --}}
                            <td style="white-space:nowrap">@unless ($product->isApproved())<button type="submit" form="apr-{{ $product->id }}" class="hapus-p" style="color:#166534;border-color:#bbf7d0">
                                <x-icon name="check" :size="11" :stroke="3" />Setujui</button> @endunless<button type="submit" form="del-{{ $product->id }}" class="hapus-p">
                                <x-icon name="trash" :size="11" />Hapus Produk</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{-- // 1.11 — Bagian 1 · Persetujuan produk — Periksa produk dan klik Setujui. Fungsi: Admin memeriksa foto, harga, dan stok produk berstatus Menunggu, lalu menekan tombol Setujui pada baris produk tersebut. --}}
            @endif
        </section>

        <div class="sticky-bar">
            <span></span>
            <div class="r">
                <a class="d-btn o" href="{{ route('admin.dashboard') }}">Batal</a>
                <button type="submit" class="d-btn" style="background:#14602f"><x-icon name="archive" :size="12" />Simpan Perubahan</button>
                <button type="submit" form="delTenant" class="d-btn red">Hapus tenant</button>
            </div>
        </div>
    </form>
    {{-- // 4.4 — Bagian 4 · Edit lapak — Kirim perubahan. Fungsi: Menyimpan perubahan profil lewat PUT. Tombol hapus memakai formulir tersendiri. --}}

    @foreach ($tenant->products as $product)
        <form id="apr-{{ $product->id }}" method="POST" action="{{ route('admin.tenant.product.approve', [$tenant, $product]) }}">@csrf @method('PATCH')</form>
        {{-- // 1.12 — Bagian 1 · Persetujuan produk — Kirim formulir persetujuan. Fungsi: Formulir produk terpilih mengirim PATCH ke rute persetujuan dengan token CSRF. --}}
        <form id="del-{{ $product->id }}" data-confirm="Produk {{ $product->name }} akan dihapus permanen." data-confirm-title="Hapus produk?" data-confirm-ok="Ya, Hapus" data-confirm-tone="danger" method="POST" action="{{ route('admin.tenant.product.destroy', [$tenant, $product]) }}">@csrf @method('DELETE')</form>
        {{-- // 5.1 — Bagian 5 · Hapus produk — Konfirmasikan penghapusan produk. Fungsi: Formulir hapus produk meminta konfirmasi dan mengirim DELETE untuk produk yang dipilih. --}}
    @endforeach
    <form id="delTenant" data-confirm="{{ $tenant->name }} beserta seluruh produknya akan dihapus permanen. Akun penjual juga dihapus jika ini lapak terakhirnya. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus tenant?" data-confirm-ok="Ya, Hapus" data-confirm-tone="danger" method="POST" action="{{ route('admin.tenant.destroy', $tenant) }}">@csrf @method('DELETE')</form>
    {{-- // 6.1 — Bagian 6 · Hapus lapak — Konfirmasikan penghapusan lapak. Fungsi: Meminta konfirmasi sebelum menghapus lapak beserta produknya. --}}
</x-layouts.dash>
