<section class="panel">
    <div class="matrix-toolbar">
        <div>
            <h2 class="panel-title">Ranking Rekomendasi</h2>
            <p class="panel-text">Ranking pertama menunjukkan dusun yang paling direkomendasikan sebagai prioritas pembangunan.</p>
        </div>
    </div>

    @if ($results->count() > 0)
        <div class="table-wrap desktop-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Kode Alternatif</th>
                        <th>Nama Dusun</th>
                        <th>Skor Dominasi</th>
                        <th>Status Prioritas</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                        <tr>
                            <td><strong>#{{ $result->ranking }}</strong></td>
                            <td><span class="code-pill">{{ $result->dusun?->kode_alternatif ?? '-' }}</span></td>
                            <td><strong>{{ $result->dusun?->nama_dusun ?? '-' }}</strong></td>
                            <td>{{ $result->skor_dominasi }}</td>
                            <td><span class="badge {{ $result->ranking === 1 ? 'badge-priority' : 'badge-info' }}">{{ $result->status_prioritas }}</span></td>
                            <td>{{ $result->keterangan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mobile-list">
            @foreach ($results as $result)
                <article class="mobile-card">
                    <div class="mobile-card-head">
                        <div>
                            <span class="code-pill">#{{ $result->ranking }}</span>
                            <h3>{{ $result->dusun?->nama_dusun ?? '-' }}</h3>
                        </div>
                        <span class="badge {{ $result->ranking === 1 ? 'badge-priority' : 'badge-info' }}">{{ $result->status_prioritas }}</span>
                    </div>
                    <dl class="meta-grid">
                        <div><dt>Kode</dt><dd>{{ $result->dusun?->kode_alternatif ?? '-' }}</dd></div>
                        <div><dt>Skor Dominasi</dt><dd>{{ $result->skor_dominasi }}</dd></div>
                    </dl>
                    <p>{{ $result->keterangan ?? '-' }}</p>
                </article>
            @endforeach
        </div>
    @else
        <p class="panel-text">Data ranking tidak tersedia.</p>
    @endif
</section>
