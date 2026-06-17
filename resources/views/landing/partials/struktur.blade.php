<section id="struktur" class="landing-section">
    <div class="landing-section-inner">
        <div class="landing-section-head">
            <span>Profil Desa</span>
            <h2>Visi, Misi, dan Struktur Organisasi</h2>
            <p>{{ $setting->nama_desa ?? 'Desa Barambang' }} menyajikan informasi perangkat desa dan arah pembangunan secara ringkas.</p>
        </div>

        <div class="landing-profile-grid">
            <article class="landing-info-box">
                <h3>Visi Desa</h3>
                <p>{{ $setting->visi ?? 'Terwujudnya desa yang maju, transparan, partisipatif, dan berbasis data dalam pembangunan.' }}</p>
            </article>
            <article class="landing-info-box">
                <h3>Misi Desa</h3>
                @php($misiItems = preg_split('/\r\n|\r|\n/', (string) ($setting->misi ?? ''), -1, PREG_SPLIT_NO_EMPTY))
                @if (count($misiItems) > 0)
                    <ul>
                        @foreach ($misiItems as $misi)
                            <li>{{ $misi }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>Misi desa belum tersedia.</p>
                @endif
            </article>
        </div>

        <div class="landing-org-grid">
            @forelse ($struktur as $item)
                <article class="landing-org-card">
                    <div class="landing-org-photo">
                        @if ($item->fotoUrl())
                            <img src="{{ $item->fotoUrl() }}" alt="Foto {{ $item->nama }}">
                        @else
                            <span>{{ strtoupper(substr($item->nama, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <h3>{{ $item->nama }}</h3>
                        <strong>{{ $item->jabatan }}</strong>
                        @if ($item->deskripsi)
                            <p>{{ $item->deskripsi }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="landing-empty">Struktur organisasi belum tersedia.</div>
            @endforelse
        </div>
    </div>
</section>
