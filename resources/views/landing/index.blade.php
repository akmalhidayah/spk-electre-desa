<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $setting->nama_desa ?? 'Desa' }} - Website Resmi Desa</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">
    <link rel="stylesheet" href="{{ asset('css/spk.css') }}?v={{ filemtime(public_path('css/spk.css')) }}">
</head>
<body class="landing-page">
    @include('landing.partials.navbar')
    @include('landing.partials.hero')
    @include('landing.partials.struktur')
    @include('landing.partials.infografis')
    @include('landing.partials.footer')

    <a href="#home" class="landing-back-top" aria-label="Kembali ke atas">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
    </a>
</body>
</html>
