@extends('layouts.app')

@section('title', 'Detail Hasil Rekomendasi - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Hasil Rekomendasi')
@section('page-title', 'Detail Hasil Rekomendasi')

@section('content')
    @php
        $topResult = $results->first();
    @endphp

    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Detail Hasil Rekomendasi</h2>
                <p>Hasil perhitungan metode ELECTRE.</p>
            </div>
            <div class="action-group">
                <a href="{{ route('admin.hasil-rekomendasi.index') }}" class="btn btn-light">Kembali</a>
                <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation) }}" class="btn btn-primary btn-auto" target="_blank">Cetak PDF</a>
            </div>
        </section>

        <section class="stat-grid">
            <article class="stat-card"><div class="stat-label">Kode Perhitungan</div><div class="stat-value stat-value-code">{{ $calculation->kode_perhitungan }}</div></article>
            <article class="stat-card"><div class="stat-label">Tahun</div><div class="stat-value">{{ $calculation->tahun }}</div></article>
            <article class="stat-card"><div class="stat-label">Status</div><div class="stat-value">{{ ucfirst($calculation->status) }}</div></article>
            <article class="stat-card"><div class="stat-label">Alternatif / Kriteria</div><div class="stat-value">{{ $calculation->total_alternatif }} / {{ $calculation->total_kriteria }}</div></article>
        </section>

        <section class="panel">
            <div class="meta-grid electre-info-grid">
                <div><dt>Judul</dt><dd>{{ $calculation->judul ?? '-' }}</dd></div>
                <div><dt>Dihitung Oleh</dt><dd>{{ $calculation->calculator?->name ?? '-' }}</dd></div>
                <div><dt>Waktu Perhitungan</dt><dd>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt>Catatan</dt><dd>{{ $calculation->notes ?? '-' }}</dd></div>
            </div>
        </section>

        @if ($topResult)
            <section class="panel priority-highlight">
                <span class="badge badge-priority">Prioritas Utama</span>
                <h2>{{ $topResult->dusun?->nama_dusun ?? '-' }}</h2>
                <p>Dusun ini memperoleh ranking pertama dengan skor dominasi {{ $topResult->skor_dominasi }}.</p>
            </section>
        @endif

        @include('admin.hasil-rekomendasi._ranking-table', ['results' => $results])

        <section class="panel">
            <h2 class="panel-title">Detail Perhitungan</h2>
            @if ($details->isEmpty())
                <p class="panel-text">Detail perhitungan tidak tersedia.</p>
            @else
                <div class="accordion-list">
                    <details open><summary>Matriks Keputusan</summary>@include('admin.electre._matrix', ['matrix' => $matriksKeputusan])</details>
                    <details><summary>Normalisasi</summary>@include('admin.electre._matrix', ['matrix' => $normalisasi['matrix'] ?? []])</details>
                    <details><summary>Pembobotan</summary>@include('admin.electre._matrix', ['matrix' => $pembobotan['matrix'] ?? []])</details>
                    <details><summary>Threshold</summary>
                        <div class="threshold-grid">
                            <div><span>Concordance</span><strong>{{ number_format((float) ($threshold['concordance'] ?? 0), 6, ',', '.') }}</strong></div>
                            <div><span>Discordance</span><strong>{{ number_format((float) ($threshold['discordance'] ?? 0), 6, ',', '.') }}</strong></div>
                        </div>
                    </details>
                    <details><summary>Aggregate Dominance Matrix</summary>@include('admin.electre._matrix', ['matrix' => $aggregateDominance])</details>
                </div>
            @endif
        </section>
    </div>
@endsection
