@php($heroUrl = $setting->heroImageUrl())

<section
    id="home"
    class="landing-hero"
    @if ($heroUrl) style="background-image: linear-gradient(90deg, rgba(0, 70, 52, 0.46) 0%, rgba(0, 70, 52, 0.34) 45%, rgba(0, 0, 0, 0.18) 100%), url('{{ $heroUrl }}')" @endif
>
    <div class="landing-hero-inner">
        <div class="landing-logo-large">
            @if ($setting->logoUrl())
                <img src="{{ $setting->logoUrl() }}" alt="Logo {{ $setting->nama_desa ?? 'desa' }}">
            @else
                <span>{{ strtoupper(substr($setting->nama_desa ?? 'D', 0, 1)) }}</span>
            @endif
        </div>
        <p class="landing-kicker">
            {{ $setting->kecamatan ?? 'Kecamatan Sinjai Borong' }} - {{ $setting->kabupaten ?? 'Kabupaten Sinjai' }}
        </p>
        <h1>{{ $setting->judul_welcome ?? 'Selamat Datang di Website Resmi Desa' }}</h1>
        <p class="landing-hero-copy">{{ $setting->deskripsi_welcome ?? 'Sistem informasi desa dan pendukung keputusan prioritas pembangunan antar dusun.' }}</p>
        <div class="landing-hero-actions">
            <a href="{{ route('login') }}" class="landing-btn landing-btn-primary">Login Aplikasi</a>
            <a href="#struktur" class="landing-btn landing-btn-light">Lihat Profil Desa</a>
        </div>
    </div>
</section>
