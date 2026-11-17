@props(["title" => "Bolo Tani", "plain" => false, "lokal" => true])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth {{ $plain ? "plain" : "" }}">
@if (! $plain)
    <header class="a-head">
        <a class="a-brand" href="{{ route('marketplace') }}">
            <span class="a-brand__mark"><x-icon name="sprout" :size="18" /></span>
            <span><b>BOLO <span>TANI</span></b><small>MARKETPLACE AGRIBISNIS{{ $lokal ? ' LOKAL' : '' }}</small></span>
        </a>
        <a class="a-nav" href="{{ route('marketplace') }}">Marketplace</a>
        <div class="a-actions">
            <a class="a-btn-o" href="{{ route('login') }}">Masuk</a>
            <a class="a-btn-f" href="{{ route('register') }}">Daftar Penjual</a>
        </div>
    </header>
    <main class="a-main">{{ $slot }}</main>
    <footer class="a-foot">
        <span>© 2026 Bolo Tani · Butuh bantuan? WhatsApp Admin <a href="https://wa.me/628123456789" target="_blank" rel="noopener">+62 812-3456-7890</a></span>
        <span>Didukung oleh Politeknik Negeri Banyuwangi · Kementerian Pertanian · Persemaian Ramadhan</span>
    </footer>
@else
    {{ $slot }}
@endif
</body>
</html>
