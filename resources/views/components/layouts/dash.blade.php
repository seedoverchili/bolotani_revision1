@props(['title' => 'Bolo Tani', 'role' => 'penjual', 'active' => 'ringkasan', 'search' => 'Cari batch panen, SKU...'])
@php
    $user = auth()->user();
    $tenant = $user?->tenant;
    $isAdmin = $role === 'admin';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
</head>
<body class="dash">
<div class="d-shell">
    <aside class="d-side">
        <div class="d-logo">
            <span class="mk"><x-icon name="sprout" :size="19" /></span>
            <span><b>BOLO <span>TANI</span></b><small>MARKETPLACE AGRIBISNIS</small></span>
        </div>

        @if ($isAdmin)
            <div class="d-label">OPERASIONAL</div>
            <nav class="d-nav dark">
                <a href="{{ route('admin.dashboard') }}" class="{{ $active === 'dashboard' ? 'on' : '' }}"><x-icon name="chart" :size="15" />Dashboard Utama</a>
            </nav>
            <div class="d-spacer"></div>
            <div class="d-user">
                <span class="av"><x-icon name="user" :size="15" /></span>
                <span><b>{{ $user->name }}</b><small>Super Admin</small></span>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Anda akan keluar dari akun ini." data-confirm-title="Keluar?" data-confirm-ok="Ya, Keluar" data-confirm-tone="danger">@csrf<button type="submit" title="Keluar"><x-icon name="log-out" :size="16" /></button></form>
            </div>
        @else
            <nav class="d-nav">
                <a href="{{ route('penjual.dashboard') }}" class="{{ $active === 'ringkasan' ? 'on' : '' }}"><x-icon name="grid" :size="15" />Ringkasan Usaha</a>
                <a href="{{ route('penjual.produk.create') }}" class="{{ $active === 'produk' ? 'on' : '' }}"><x-icon name="package" :size="15" />Marketplace Produk Tani</a>
            </nav>
            <div class="d-spacer"></div>
            <a class="d-back" href="{{ route('marketplace') }}"><x-icon name="arrow-left" :size="14" />Ke Marketplace</a>
            <div class="d-badge">
                <x-icon name="shield" :size="18" />
                <span><b>Mitra Aktif</b><small>Politeknik Banyuwangi</small></span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:8px" data-confirm="Anda akan keluar dari akun ini." data-confirm-title="Keluar?" data-confirm-ok="Ya, Keluar" data-confirm-tone="danger">@csrf
                <button type="submit" class="d-back" style="width:100%"><x-icon name="log-out" :size="14" />Keluar</button>
            </form>
        @endif
    </aside>

    <main class="d-main">
        <div class="d-top">
            <div class="d-search"><x-icon name="search" :size="14" /><input type="search" placeholder="{{ $isAdmin ? 'Cari data tenant, produk, audit...' : $search }}" aria-label="Cari" data-filter-input></div>
            @if ($isAdmin)
                <div class="d-tools"><span class="d-bell"><x-icon name="bell" :size="14" /></span><span class="d-avatar"><x-icon name="user" :size="14" /></span></div>
            @endif
        </div>
        {{ $slot }}
    </main>
</div>
<script src="{{ asset('js/ui.js') }}"></script>
</body>
</html>
