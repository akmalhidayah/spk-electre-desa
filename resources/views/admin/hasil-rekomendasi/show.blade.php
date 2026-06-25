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
                <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation->tahun) }}" class="btn btn-primary btn-auto" target="_blank">Cetak PDF</a>
            </div>
        </section>

        @if ($topResult)
            <section class="panel priority-highlight">
                <span class="badge badge-priority">Prioritas Utama</span>
                <h2>{{ $topResult->dusun?->nama_dusun ?? '-' }}</h2>
                <p>Dusun ini memperoleh ranking pertama dengan skor dominasi {{ $topResult->skor_dominasi }}.</p>
            </section>
        @endif

        @include('admin.hasil-rekomendasi._ranking-table', [
            'results' => $results,
            'calculation' => $calculation,
            'dusunPdfRouteName' => 'admin.hasil-rekomendasi.dusun-pdf',
        ])

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
