@php
    $dusunPdfRouteName = $dusunPdfRouteName ?? null;
    $showDusunPdf = isset($calculation) && $dusunPdfRouteName;
@endphp

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
                        @if ($showDusunPdf)
                            <th class="text-right">PDF Usulan</th>
                        @endif
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
                            @if ($showDusunPdf)
                                <td>
                                    @if ($result->dusun)
                                        <div class="action-group icon-actions">
                                            <a href="{{ route($dusunPdfRouteName, [$calculation, $result->dusun]) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF usulan {{ $result->dusun->nama_dusun }}" aria-label="Cetak PDF usulan {{ $result->dusun->nama_dusun }}">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                                            </a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
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
                    @if ($showDusunPdf && $result->dusun)
                        <div class="mobile-actions icon-actions">
                            <a href="{{ route($dusunPdfRouteName, [$calculation, $result->dusun]) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF usulan {{ $result->dusun->nama_dusun }}" aria-label="Cetak PDF usulan {{ $result->dusun->nama_dusun }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                            </a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <p class="panel-text">Data ranking tidak tersedia.</p>
    @endif
</section>
