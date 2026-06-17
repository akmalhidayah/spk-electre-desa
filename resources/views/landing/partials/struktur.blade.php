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

        <?php
            $strukturGroups = $struktur->groupBy(function ($item) {
                return match (true) {
                    $item->jabatan === 'Kepala Desa' => 'Pimpinan Desa',
                    $item->jabatan === 'Sekretaris Desa' => 'Sekretariat Desa',
                    str_contains($item->jabatan, 'Seksi') => 'Kepala Seksi',
                    str_contains($item->jabatan, 'Kaur') => 'Kepala Urusan',
                    str_contains($item->jabatan, 'Dusun') => 'Kepala Dusun',
                    default => 'Perangkat Desa',
                };
            });

            $groupOrder = [
                'Pimpinan Desa',
                'Sekretariat Desa',
                'Kepala Seksi',
                'Kepala Urusan',
                'Kepala Dusun',
                'Perangkat Desa',
            ];
        ?>

        @if ($strukturGroups->isEmpty())
            <div class="landing-empty">Struktur organisasi belum tersedia.</div>
        @else
            @foreach ($groupOrder as $groupName)
                @if ($strukturGroups->has($groupName))
                    <div class="landing-org-group">
                        <h3>{{ $groupName }}</h3>
                        <div class="landing-org-grid">
                            @foreach ($strukturGroups->get($groupName) as $item)
                                <article class="landing-org-card">
                                    <div class="landing-org-photo">
                                        @if ($item->fotoUrl())
                                            <img src="{{ $item->fotoUrl() }}" alt="Foto {{ $item->nama }}">
                                        @elseif ($setting->logoUrl())
                                            <img src="{{ $setting->logoUrl() }}" alt="Logo {{ $setting->nama_desa ?? 'desa' }}" class="landing-org-logo">
                                        @else
                                            <img src="{{ asset('images/logo-kiri.png') }}" alt="Logo desa" class="landing-org-logo">
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
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</section>
