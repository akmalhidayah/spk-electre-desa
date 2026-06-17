@php($heroUrl = $setting->heroImageUrl())

<section
    id="home"
    class="landing-hero"
    @if ($heroUrl) style="background-image: linear-gradient(90deg, rgba(0, 70, 52, 0.46) 0%, rgba(0, 70, 52, 0.34) 45%, rgba(0, 0, 0, 0.18) 100%), url('{{ $heroUrl }}')" @endif
>
    <div class="landing-hero-inner">
        <p class="landing-kicker">
            {{ $setting->kecamatan ?? 'Kecamatan Sinjai Borong' }} - {{ $setting->kabupaten ?? 'Kabupaten Sinjai' }}
        </p>
        <h1>{{ $setting->judul_welcome ?? 'Selamat Datang di Website Resmi Desa' }}</h1>
    </div>
</section>
