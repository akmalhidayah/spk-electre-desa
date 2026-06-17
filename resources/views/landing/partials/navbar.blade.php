<header class="landing-navbar floating-navbar">
    <a href="#home" class="landing-brand" aria-label="Beranda">
        @if ($setting->logoUrl())
            <img src="{{ $setting->logoUrl() }}" alt="Logo {{ $setting->nama_desa ?? 'desa' }}">
        @else
            <span>{{ strtoupper(substr($setting->nama_desa ?? 'D', 0, 1)) }}</span>
        @endif
        <strong>{{ $setting->nama_desa ?? 'Desa Barambang' }}</strong>
    </a>

    <nav class="landing-menu" aria-label="Navigasi landing page">
        <a href="#home">Home</a>
        <a href="#struktur">Struktur Organisasi</a>
        <a href="#infografis">Infografis</a>
        <a href="{{ route('login') }}" class="landing-login-link">Login</a>
    </nav>
</header>
