@php
    $tones = [
        0 => 'background:#005f40;color:#fff',
        1 => 'background:#416656;color:#fff',
        2 => 'background:#317853;color:#b4fdcf',
        3 => 'background:#c3ecd7;color:#476c5b',
    ];
@endphp
<main class="m3">
    <nav class="crumbs">
        <a href="{{ route('marketplace') }}"><img src="{{ asset('images/icons/m3-home.svg') }}" alt="" width="12" height="14">Beranda</a>
        <img src="{{ asset('images/icons/m3-chev.svg') }}" alt="" width="5" height="8">
        <a href="{{ route('marketplace') }}">Marketplace</a>
        <img src="{{ asset('images/icons/m3-chev.svg') }}" alt="" width="5" height="8">
        @if ($tenant->kecamatan)
            <a href="{{ route('marketplace', ['cari' => $tenant->kecamatan]) }}">Kecamatan {{ $tenant->kecamatan }}</a>
            <img src="{{ asset('images/icons/m3-chev.svg') }}" alt="" width="5" height="8">
        @endif
        <b>Lapak {{ $tenant->name }}</b>
    </nav>

    <section class="m3-head">
        <div class="store-card">
            <div class="store-cover">
                <img src="{{ $tenant->coverUrl() }}" alt="Sampul {{ $tenant->name }}" style="object-position:50% {{ $tenant->cover_pos }}%">
                @if ($tenant->approved_at)<div class="store-status"><i></i>Mitra Bolo Tani sejak {{ $tenant->approved_at->translatedFormat('F Y') }}</div>@endif
            </div>
            <div class="store-bar">
                <div class="store-id">
                    <div class="store-id__left">
                        <div class="store-avatar-slot">
                            <div class="store-avatar">
                                <div class="ring">
                                    @if ($tenant->avatarLargeUrl())
                                        <img src="{{ $tenant->avatarLargeUrl() }}" alt="{{ $tenant->owner_name }}">
                                    @endif
                                </div>
                                @if ($isOwner)
                                    <a class="cam" href="{{ route('penjual.foto') }}" title="Ganti foto"><img src="{{ asset('images/icons/m3-camera.svg') }}" alt="" width="16" height="16"></a>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h1>{{ $tenant->name }}</h1>
                            <div class="line"><img src="{{ asset('images/icons/m3-user.svg') }}" alt="" width="12" height="12">Pengelola: <b>{{ $tenant->owner_name }}</b></div>
                            <div class="line addr"><img src="{{ asset('images/icons/m3-pin-sm.svg') }}" alt="" width="11" height="13"><span>{{ $tenant->address }}</span></div>
                        </div>
                    </div>
                    <a class="btn-wa-lg" href="{{ route('wa.tenant', $tenant) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('images/icons/m3-chat-white.svg') }}" alt="">Hubungi via WhatsApp
                    </a>
                </div>
                <div class="metrics">
                    <div class="metric">
                        <div class="ic"><img src="{{ asset('images/icons/m3-shield.svg') }}" alt="" width="17" height="22"></div>
                        <div><b>100%</b><small>Bebas Potongan Biaya</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="tabs"><div><button type="button"><img src="{{ asset('images/icons/m3-tab-store.svg') }}" alt="Katalog" width="17" height="15"></button></div></div>

    <div class="m3-work">
        <div class="m3-left">
            <div class="filter-card">
                <div class="sf">
                    <input type="text" wire:model.live.debounce.300ms="q" placeholder="Cari produk di lapak {{ $tenant->name }}..." autocomplete="off">
                    <img src="{{ asset('images/icons/m3-search.svg') }}" alt="">
                </div>
                <div class="fpills">
                    <button type="button" wire:click="setGroup('')" class="{{ $group === '' ? 'on' : '' }}">Semua Komoditas ({{ $total }})</button>
                    @foreach ($groups as $name => $count)
                        <button type="button" wire:click="setGroup('{{ $name }}')" wire:key="g-{{ $name }}" class="{{ $group === $name ? 'on' : '' }}">{{ $name }} ({{ $count }})</button>
                    @endforeach
                </div>
            </div>

            <div class="pgrid">
                @forelse ($products as $product)
                    <article class="pcard {{ $product->is_featured ? 'feat' : '' }}" wire:key="pc-{{ $product->id }}">
                        <div class="pcard__img">
                            <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}">
                            @if ($product->badge)<span class="pcard__tag" style="{{ $tones[$product->badge_tone] ?? $tones[0] }}">{{ $product->badge }}</span>@endif
                            @if ($product->is_featured)<span class="pcard__heart"><img src="{{ asset('images/icons/m3-heart.svg') }}" alt="" width="16" height="16"></span>@endif
                        </div>
                        <div class="pcard__body">
                            <div>
                                <div class="pcard__cat"><b>{{ $product->subcategory }}</b><span>•</span><span>{{ $product->tags }}</span></div>
                                <h2>{{ $product->name }}</h2>
                                <p>{{ \Illuminate\Support\Str::limit($product->descriptionText(), 120) }}</p>
                            </div>
                            <div class="pcard__foot">
                                <div>
                                    <small>Harga Langsung Petani:</small>
                                    <div class="pr"><b>{{ $product->priceFormatted() }}</b><span>/ {{ \Illuminate\Support\Str::before($product->unit, ' ') }}</span></div>
                                </div>
                                <a class="pcard__btn" href="{{ route('produk.show', $product->slug) }}" wire:navigate>Detail Produk<img src="{{ asset('images/icons/m3-arrow.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state" style="grid-column:1/-1">Belum ada komoditas yang cocok.</div>
                @endforelse
            </div>
        </div>

        <aside class="m3-right">
            <div class="loc-card">
                <h2><img src="{{ asset('images/icons/m3-pin-lg.svg') }}" alt=""><span>Lokasi<br>Lapak</span></h2>
                @if ($tenant->lat && $tenant->lng)<a class="loc-btn" href="{{ $tenant->mapsLink() }}" target="_blank" rel="noopener"><img src="{{ asset('images/icons/m3-route.svg') }}" alt="">Buka Rute di Google Maps</a>@endif
            </div>
            <div class="consult">
                <div class="consult__head">
                    <div class="consult__ic"><img src="{{ asset('images/icons/m3-wa-circle.svg') }}" alt="" width="18" height="16"></div>
                    <div>
                        <h2>Konsultasi Gratis</h2>
                        <small>{{ explode(' ', $tenant->owner_name)[0] }} langsung siap menjawab</small>
                    </div>
                </div>
                <p>Ada pertanyaan soal produk, stok, atau pengiriman? Diskusikan langsung dengan penjual melalui WhatsApp.</p>
                <a href="{{ route('wa.tenant', $tenant) }}" target="_blank" rel="noopener"><img src="{{ asset('images/icons/m3-wa-btn.svg') }}" alt="">Mulai Chat WhatsApp Sekarang</a>
            </div>
        </aside>
    </div>
</main>
