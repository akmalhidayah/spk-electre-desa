<section id="infografis" class="landing-section landing-section-muted">
    <div class="landing-section-inner">
        <div class="landing-section-head">
            <span>Infografis</span>
            <h2>{{ $setting->judul_infografis ?? 'Infografis Desa' }}</h2>
            <p>{{ $setting->deskripsi_infografis ?? 'Informasi wilayah dan peta desa.' }}</p>
        </div>

        <div class="landing-map-panel">
            @if ($setting->maps_embed)
                <div class="landing-map-frame">
                    {!! $setting->maps_embed !!}
                </div>
            @elseif ($setting->gambarPetaUrl())
                <img src="{{ $setting->gambarPetaUrl() }}" alt="Peta {{ $setting->nama_desa ?? 'desa' }}" class="landing-map-image">
            @else
                <div class="landing-map-empty">Peta desa belum tersedia.</div>
            @endif

            @if ($setting->maps_link)
                <a href="{{ $setting->maps_link }}" target="_blank" rel="noopener noreferrer" class="landing-btn landing-btn-outline">Buka Google Maps</a>
            @endif
        </div>
    </div>
</section>
