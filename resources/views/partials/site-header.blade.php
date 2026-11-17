<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="{{ route('marketplace') }}">
            <span class="brand__mark"><img src="{{ asset('images/icons/sprout-white.svg') }}" alt=""></span>
            <span>
                <span class="brand__name">BOLO<span>TANI</span></span>
                <span class="brand__tag" style="display:block">MARKETPLACE AGRIBISNIS </span>
            </span>
        </a>
        <nav class="site-nav"><a href="{{ route('marketplace') }}">Marketplace</a></nav>
        <div class="site-actions">
            @auth
                <a class="pill-btn" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('penjual.dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="pill-btn ghost">Keluar</button>
                </form>
            @else
                <a class="pill-btn" href="{{ route('login') }}">Masuk ke penjual</a>
                <a class="pill-btn" href="{{ route('register') }}">Daftar penjual</a>
            @endauth
        </div>
    </div>
</header>
