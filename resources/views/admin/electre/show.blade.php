@extends('layouts.app')

@section('title', 'Hasil ELECTRE - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Proses ELECTRE')
@section('page-title', 'Hasil Perhitungan ELECTRE')

@section('content')
    @php
        $matriksKeputusan = $details->get('matriks_keputusan')?->data ?? [];
        $normalisasi = $details->get('normalisasi')?->data ?? [];
        $pembobotan = $details->get('pembobotan')?->data ?? [];
        $threshold = $details->get('threshold')?->data ?? [];
        $aggregateDominance = $details->get('aggregate_dominance')?->data ?? [];
    @endphp

    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Hasil Perhitungan ELECTRE</h2>
                <p>Detail hasil rekomendasi prioritas pembangunan.</p>
            </div>
            <a href="{{ route('admin.electre.index', ['tahun' => $calculation->tahun]) }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            <div class="matrix-toolbar">
                <div>
                    <h2 class="panel-title">Ranking Rekomendasi</h2>
                    <p class="panel-text">Ranking dihitung dari skor dominasi aggregate matrix, lalu total nilai terbobot sebagai tie breaker.</p>
                </div>
                <a href="{{ route('admin.electre.index') }}" class="btn btn-light">Proses Tahun Lain</a>
            </div>

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
                        @foreach ($calculation->results->sortBy('ranking') as $result)
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
                @foreach ($calculation->results->sortBy('ranking') as $result)
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
        </section>

        <section class="panel">
            <h2 class="panel-title">Detail Perhitungan</h2>
            <div class="accordion-list">
                <details open>
                    <summary>Matriks Keputusan</summary>
                    @include('admin.electre._matrix', ['matrix' => $matriksKeputusan])
                </details>

                <details>
                    <summary>Matriks Normalisasi</summary>
                    @include('admin.electre._matrix', ['matrix' => $normalisasi['matrix'] ?? []])
                </details>

                <details>
                    <summary>Matriks Ternormalisasi Terbobot</summary>
                    @include('admin.electre._matrix', ['matrix' => $pembobotan['matrix'] ?? []])
                </details>

                <details>
                    <summary>Threshold Concordance & Discordance</summary>
                    <div class="threshold-grid">
                        <div><span>Concordance</span><strong>{{ number_format((float) ($threshold['concordance'] ?? 0), 6, ',', '.') }}</strong></div>
                        <div><span>Discordance</span><strong>{{ number_format((float) ($threshold['discordance'] ?? 0), 6, ',', '.') }}</strong></div>
                    </div>
                </details>

                <details>
                    <summary>Matriks Aggregate Dominance</summary>
                    @include('admin.electre._matrix', ['matrix' => $aggregateDominance])
                </details>
            </div>
        </section>
    </div>
@endsection
