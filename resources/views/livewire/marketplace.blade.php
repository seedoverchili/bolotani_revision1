<main class="m1">
    <section class="m1-hero">
        <div class="wrap">
            <div class="m1-top">
                <nav class="m1-crumb">
                    <a href="{{ route('marketplace') }}"><img src="{{ asset('images/icons/home.svg') }}" alt="" width="11" height="12">Beranda</a>
                    <img src="{{ asset('images/icons/chevron-right-sm.svg') }}" alt="" width="4" height="6">
                    <b>Marketplace</b>
                </nav>
                <div class="m1-pill"><i></i><span>PASAR TERBUKA AGRIBISNIS INdonesia</span></div>
            </div>
            <div class="m1-title">
                <h1>Pasar Tani Digital <u>Bolo Tani</u></h1>
                <p>Temukan produk segar, bibit unggul bersertifikat, olahan artisan organik, dan alat pertanian modern<br>langsung dari tangan pertama petani lokal tanpa perantara.</p>
            </div>
            <div class="m1-pills">
                <button type="button" wire:click="setCategory('')" class="cat-pill {{ $category === '' ? 'on' : '' }}">
                    @if ($category === '')<img src="{{ asset('images/icons/grid.svg') }}" alt="">@endif
                    Semua Komoditas
                </button>
                @foreach (\App\Models\Product::CATEGORIES as $key => $label)
                    <button type="button" wire:click="setCategory('{{ $key }}')" class="cat-pill {{ $category === $key ? 'on' : '' }}">
                        {{ $key === 'hasil_pertanian' ? 'Hasil Pertanian Segar' : ($key === 'alat_pertanian' ? 'Alat & Sarana Tani' : $label) }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="m1-work">
        <div class="wrap">
            <form class="search-card" wire:submit.prevent>
                <div class="search-field">
                    <input type="text" wire:model.live.debounce.300ms="q" placeholder="Cari komoditas, bibit durian, alpukat, beras organik, atau nama pedagang..." autocomplete="off">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="">
                    <button type="submit">Cari</button>
                </div>
            </form>

            <div class="agro-grid">
                @forelse ($products as $product)
                    <article class="agro-card" wire:key="p-{{ $product->id }}" style="background-image: url('{{ $product->coverUrl() }}')">
                        <div class="top">
                            <span class="badge-cat {{ in_array($product->category, ['produk_olahan', 'hasil_pertanian']) && $product->category === 'produk_olahan' ? 'alt' : '' }}">{{ $product->categoryLabel() }}</span>
                        </div>
                        <div class="body">
                            <h3>{{ $product->name }}</h3>
                            <div class="price"><b>{{ $product->priceFormatted() }}</b><small>/ {{ $product->unit }}</small></div>
                            <div class="desc">{{ \Illuminate\Support\Str::limit($product->descriptionText(), 34, '…') }}</div>
                            <div class="meta">
                                <div>
                                    <img src="{{ asset('images/icons/store.svg') }}" alt="" width="13" height="12">
                                    <span class="shop">{{ $product->tenant->name }}</span>
                                </div>
                                <div>
                                    <img src="{{ asset('images/icons/pin.svg') }}" alt="" width="8" height="10">
                                    <span class="loc">{{ $product->tenant->kecamatan ? 'Kec. '.$product->tenant->kecamatan : 'Banyuwangi' }}</span>
                                </div>
                            </div>
                            <div class="acts">
                                <a class="detail" href="{{ route('produk.show', $product->slug) }}" wire:navigate>Detail &amp; Peta</a>
                                <a class="wa" href="{{ route('wa.redirect', $product) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('images/icons/chat-dark.svg') }}" alt="">Hubungi WA
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Tidak ada komoditas yang cocok dengan pencarian Anda.</div>
                @endforelse
            </div>

            @if ($products->lastPage() > 1)
                <div class="pager-wrap">
                    <div class="pager">
                        <button type="button" wire:click="gotoPage(1)" @disabled($products->onFirstPage())><img src="{{ asset('images/icons/page-first.svg') }}" alt="Halaman pertama"></button>
                        <button type="button" wire:click="previousPage" @disabled($products->onFirstPage())><img src="{{ asset('images/icons/page-prev.svg') }}" alt="Sebelumnya"></button>
                        @for ($i = 1; $i <= $products->lastPage(); $i++)
                            <button type="button" wire:click="gotoPage({{ $i }})" class="{{ $i === $products->currentPage() ? 'on' : '' }}">{{ $i }}</button>
                        @endfor
                        <button type="button" wire:click="nextPage" @disabled(! $products->hasMorePages())><img src="{{ asset('images/icons/page-next.svg') }}" alt="Berikutnya"></button>
                        <button type="button" wire:click="gotoPage({{ $products->lastPage() }})" @disabled(! $products->hasMorePages())><img src="{{ asset('images/icons/page-last.svg') }}" alt="Halaman terakhir"></button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="cta">
        <div class="cta__card">
            <div class="cta__rings"><img src="{{ asset('images/icons/ring-circles.svg') }}" alt=""></div>
            <div class="cta__text">
                <h2>Mulai Perjalanan<br><span>Tani-mu</span> Sekarang</h2>
                <p>Bergabung dengan ribuan petani digital Indonesia.<br>pantau harga pangan komoditas real-time, dan perluas jangkauan pasar<br>tanpa potongan komisi yang memberatkan.</p>
            </div>
            <div class="cta__metric">
                <div class="ic"><img src="{{ asset('images/icons/shield-dark.svg') }}" alt="" width="13" height="17"></div>
                <div><b>100% Petani Asli</b><small>Terverifikasi Kelembagaan</small></div>
            </div>
            <a class="pill-btn cta__btn" style="left:64px" href="{{ route('login') }}">Masuk ke penjual</a>
            <a class="pill-btn cta__btn" style="left:253px" href="{{ route('register') }}">Daftar penjual</a>
        </div>
    </section>
</main>
