@php
    $tenant = $product->tenant;
    $images = $product->images;
    $current = $images[$active] ?? $images->first();
@endphp
<main style="background:var(--bg)">
    <div class="m2-main">
        <div class="m2-grid">
            <div class="m2-left">
                <div class="gallery">
                    <div class="gallery__main">
                        @if ($current)<img class="photo" src="{{ asset($current->path) }}" alt="{{ $product->name }}">@endif
                        <span class="gallery__badge">{{ $product->categoryLabel() }}</span>
                        <span class="gallery__count">{{ $images->isEmpty() ? 0 : $active + 1 }}/{{ $images->count() }} Foto</span>
                    </div>
                    @if ($images->count() > 1)
                        <div class="thumbs">
                            @foreach ($images as $i => $image)
                                <button type="button" wire:click="select({{ $i }})" wire:key="th-{{ $image->id }}" class="{{ $i === $active ? 'on' : '' }}">
                                    <img src="{{ asset($image->path) }}" alt="Foto {{ $i + 1 }}">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="title-card">
                    <div>
                        <h1>{{ $product->name }}</h1>
                        <div class="desc">{!! $product->descriptionHtml() !!}</div>
                    </div>
                    <div class="price-block">
                        <small>Harga Langsung Petani:</small>
                        <div><b>{{ $product->priceFormatted() }}</b><span>/ {{ $product->unit }}</span></div>
                    </div>
                </div>
            </div>

            <div class="m2-right">
                <div class="card12 merchant">
                    <div class="merchant__head">
                        <div class="merchant__avatar">
                            @if ($tenant->avatarUrl())<img src="{{ $tenant->avatarUrl() }}" alt="{{ $tenant->owner_name }}">@endif
                        </div>
                        <div>
                            <span class="tag-mini">PEDAGANG</span>
                            <h2><a href="{{ route('pedagang.show', $tenant->slug) }}" wire:navigate>{{ $tenant->name }}</a></h2>
                            <p class="by">Pengelola: {{ $tenant->owner_name }}</p>
                        </div>
                    </div>
                    <div class="addr">
                        <img src="{{ asset('images/icons/pin-footer.svg') }}" alt="">
                        <span>{{ $tenant->address }}</span>
                    </div>
                    <div class="direct">
                        <div class="direct__note">
                            <img src="{{ asset('images/icons/m2-info.svg') }}" alt="">
                            <span><b>Tanpa Biaya Admin &amp; Bebas Perantara.</b> Transaksi, konsultasi perawatan, dan negosiasi ongkos kirim disepakati langsung bersama petani secara transparan.</span>
                        </div>
                        <a class="wa-main" href="{{ route('wa.redirect', $product) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('images/icons/m2-chat-white.svg') }}" alt="">Hubungi via WhatsApp
                        </a>
                        <div style="padding-top:18px" x-data="{ done: false }">
                            <button type="button" class="btn-soft" x-on:click="navigator.clipboard.writeText('{{ '+'.$tenant->waNumber() }}'); done = true; setTimeout(() => done = false, 1800)">
                                <img src="{{ asset('images/icons/m2-copy.svg') }}" alt="">
                                <span x-text="done ? 'Nomor Tersalin' : 'Salin Nomor'">Salin Nomor</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card12 map-card">
                    <h3><img src="{{ asset('images/icons/m2-pin-lg.svg') }}" alt="">Lokasi Lapak</h3>
                    @if ($tenant->lat && $tenant->lng)<a class="btn-soft green" href="{{ $tenant->mapsLink() }}" target="_blank" rel="noopener">
                        <img src="{{ asset('images/icons/m2-route.svg') }}" alt="">Buka Rute di Google Maps
                    </a>@endif
                </div>
            </div>
        </div>
    </div>

    <section class="related">
        <div class="wrap">
            <h3>Produk Tani Lain</h3>
            <div class="tiles">
                @foreach ($related as $item)
                    <article class="tile" wire:key="rel-{{ $item->id }}">
                        <img src="{{ $item->coverUrl() }}" alt="{{ $item->name }}">
                        <div><span class="chip">{{ $item->categoryLabel() }}</span></div>
                        <div class="meta">
                            <h4>{{ $item->name }}</h4>
                            <div class="price"><b>{{ $item->priceFormatted() }}</b><small>/{{ $item->unit }}</small></div>
                            <div class="foot">
                                <span><img src="{{ asset('images/icons/m2-user-sm.svg') }}" alt="">{{ $item->tenant->name }}</span>
                                <a href="{{ route('produk.show', $item->slug) }}" wire:navigate>Detail<img src="{{ asset('images/icons/m2-arrow-sm.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
