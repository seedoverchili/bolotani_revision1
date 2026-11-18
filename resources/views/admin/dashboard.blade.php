@php
    $colors = ['#86efac', '#a7f3d0', '#bbf7d0', '#c7d2fe', '#fde68a', '#fbcfe8'];
@endphp
{{-- // 3.8 — Bagian 3 · Dashboard — Siapkan penanda toko. Fungsi: Menyiapkan warna inisial toko agar baris mudah dibedakan. --}}
<x-layouts.dash title="Dashboard Monitoring — Bolo Tani Admin" role="admin" active="dashboard">
    <div class="a-bread">Admin Agribisnis › <b>Bolo Tani Konsol</b></div>
    <h1 class="a-h1">Dashboard Monitoring Agribisnis</h1>
    <p class="a-sub">Pantau lapak terdaftar dan produk yang perlu diperiksa.</p>

    @if (session('ok'))<div class="d-alert ok" style="margin-top:14px"><x-icon name="circle-check" :size="16" /><span>{{ session('ok') }}</span></div>@endif

    <div class="a-stats">
        <div class="a-stat">
            <div>
                <small>TOTAL TENANT TERDAFTAR</small>
                <div class="v">
                    <b>{{ number_format($tenantCount, 0, ',', '.') }}</b>
                    <span>Toko</span>
                </div>
            </div>
            <span class="ico g">
                <x-icon name="store" :size="20" />
            </span>
        </div>
        <div class="a-stat">
            <div>
                <small>PRODUK DISETUJUI DI LAPAK AKTIF</small>
                <div class="v">
                    <b>{{ number_format($productCount, 0, ',', '.') }}</b>
                    <span>Produk</span>
                </div>
            </div>
            <span class="ico p">
                <x-icon name="sprout" :size="20" />
            </span>
        </div>
    </div>
    {{-- // 3.9 — Bagian 3 · Dashboard — Tampilkan angka ringkasan. Fungsi: Menampilkan total lapak dan jumlah produk yang telah lolos persetujuan di lapak aktif. --}}

    <section class="d-card" style="margin-top:18px">
        <h2 style="display:flex;align-items:center;gap:8px;font-size:15px"><x-icon name="chart" :size="14" />Pertumbuhan Tenant</h2>
        <p class="hint">Rekapitulasi tren pendaftaran 6 bulan terakhir · batang pertama: terdaftar, batang kedua: aktif.</p>
        <div class="chart-wrap">
            <div class="chart">
                @foreach ($months as $i => $m)
                    @php $last = $loop->last; $mid = $i >= 3 && ! $last; @endphp
                    <div class="col {{ $last ? 'last' : ($mid ? 'mid' : '') }}">
                        <div class="bars" style="position:relative">
                            @if ($last)<span class="tip" style="left:50%;transform:translateX(-50%);top:-20px">{{ $m['registered'] }} Toko</span>@endif
                            <i class="a" style="height:{{ round($m['registered'] / $maxBar * 100) }}%"></i>
                            <i class="b" style="height:{{ round($m['approved'] / $maxBar * 100) }}%"></i>
                        </div>
                        <span class="lab">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    {{-- // 3.10 — Bagian 3 · Dashboard — Gambarkan tren bulanan. Fungsi: Menggambar dua batang per bulan dari data controller dengan skala yang sama. --}}

    <section class="d-card" id="tenants">
        <h2 style="display:flex;align-items:center;gap:8px;font-size:15px"><x-icon name="settings" :size="14" />Daftar Tenant</h2>
        <p class="hint">{{ $pendingCount }} produk menunggu persetujuan di seluruh lapak.</p>
        <p class="hint">Tenant yang mendaftar langsung aktif. Produk baru dari tenant tayang setelah Anda setujui di halaman detail tenant.</p>
        <table class="a-table">
            <thead><tr><th>NAMA TENANT / TOKO</th><th>TANGGAL<br>DAFTAR</th><th>KECAMATAN</th><th>PRODUK</th><th>AKSI</th></tr></thead>
            <tbody>
            @forelse ($tenants as $tenant)
                <tr data-filter-row>
                    <td>
                        <div class="tn">
                            <span class="in" style="background:{{ $colors[$tenant->id % count($colors)] }}">{{ $tenant->initials() }}</span>
                            <span><b>{{ $tenant->name }}</b><small>ID: {{ $tenant->code }}</small></span>
                        </div>
                    </td>
                    <td class="dt">
                        <b>{{ $tenant->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') }}</b>
                        <small>{{ $tenant->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</small>
                    </td>
                    <td>{{ $tenant->kecamatan }}</td>
                    <td>{{ $tenant->products_count }} produk
                        @if ($tenant->pending_products_count)<div class="em" style="color:#b45309">{{ $tenant->pending_products_count }} menunggu persetujuan</div>@endif</td>
                    <td>
                        <div class="acts">
                            <a class="ab o" href="{{ route('admin.tenant.show', $tenant) }}"><x-icon name="pencil" :size="10" />Detail &amp; Produk</a>
                            <form method="POST" action="{{ route('admin.tenant.destroy', $tenant) }}" data-confirm="{{ $tenant->name }} beserta seluruh produknya akan dihapus permanen. Akun penjual juga dihapus jika ini lapak terakhirnya. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus tenant?" data-confirm-ok="Ya, Hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button type="submit" class="ab r">Hapus Tenant</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada lapak yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{-- // 1.9 — Bagian 1 · Persetujuan produk — Pilih lapak yang perlu diperiksa. Fungsi: Admin melihat jumlah yang menunggu pada setiap lapak dan membuka Detail & Produk untuk memeriksanya. --}}
        @if ($tenants->hasPages())
            <div class="pager-a">
                @foreach ($tenants->getUrlRange(1, $tenants->lastPage()) as $page => $url)
                    @if ($page === $tenants->currentPage())<span class="cur">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
                @endforeach
            </div>
        @endif
        {{-- // 3.11 — Bagian 3 · Dashboard — Pertahankan pencarian saat berpindah halaman. Fungsi: Tautan halaman membawa kata kunci pencarian agar hasil tetap konsisten. --}}
    </section>
</x-layouts.dash>
