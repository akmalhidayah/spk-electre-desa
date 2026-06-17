<header class="landing-navbar floating-navbar">
    <a href="#home" class="landing-brand" aria-label="Beranda">
        @if ($setting->logoUrl())
            <img src="{{ $setting->logoUrl() }}" alt="Logo {{ $setting->nama_desa ?? 'desa' }}">
        @else
            <span>{{ strtoupper(substr($setting->nama_desa ?? 'D', 0, 1)) }}</span>
        @endif
        <strong>{{ $setting->nama_desa ?? 'Desa Barambang' }}</strong>
    </a>

    <nav class="landing-menu landing-menu-desktop" aria-label="Navigasi landing page">
        <a href="#home">Home</a>
        <a href="#struktur">Struktur Organisasi</a>
        <a href="#infografis">Infografis</a>
        <a href="{{ route('login') }}" class="landing-login-link">Login</a>
    </nav>

    <details class="landing-menu-panel landing-menu-mobile">
        <summary class="landing-menu-toggle" aria-label="Buka menu navigasi">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </summary>
        <nav class="landing-menu landing-menu-dropdown" aria-label="Navigasi landing page mobile">
            <a href="#home">Home</a>
            <a href="#struktur">Struktur Organisasi</a>
            <a href="#infografis">Infografis</a>
            <a href="{{ route('login') }}" class="landing-login-link">Login</a>
        </nav>
    </details>
</header>
