@extends('layouts.app')

@section('title', 'Rekomendasi Prioritas Pembangunan - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa / Hasil Rekomendasi')
@section('page-title', 'Rekomendasi Prioritas Pembangunan')

@section('content')
    @php
        $topResult = $results->first();
        $keputusanAkhir = null;

        if (class_exists(\App\Models\KeputusanAkhir::class) && \Illuminate\Support\Facades\Schema::hasTable('keputusan_akhirs') && method_exists($calculation, 'keputusanAkhir')) {
            $keputusanAkhir = $calculation->keputusanAkhir;
        }
    @endphp

    <div class="stack">
        <x-budget-summary :summary="$budgetSummary" />
        @if ($budgetSummary['pagu'] === null)<div class="alert alert-warning">Pagu anggaran pembangunan tahun ini belum diatur.</div>@endif
        @if ($needsRecalculation)
            <div class="alert alert-warning">
                <strong>Perlu dihitung ulang.</strong>
                Data perencanaan telah berubah. Hasil rekomendasi ini merupakan perhitungan sebelumnya dan perlu dihitung ulang sebelum penetapan keputusan baru. Menunggu Admin melakukan perhitungan ELECTRE terbaru.
            </div>
        @endif
        <section class="page-header-card">
            <div>
                <h2>Rekomendasi Prioritas Pembangunan</h2>
                <p>Hasil perangkingan program pembangunan berdasarkan metode ELECTRE.</p>
            </div>
            <div class="action-group">
                <a href="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="btn btn-light">Kembali</a>
                <a href="{{ route('kepala-desa.hasil-rekomendasi.pdf', $calculation->tahun) }}" class="btn btn-primary btn-auto" target="_blank">Cetak Laporan</a>
                @if ($keputusanAkhir)
                    <span class="badge badge-success">Keputusan akhir sudah dibuat</span>
                    <a href="{{ route('kepala-desa.keputusan-akhir.show', $keputusanAkhir) }}" class="btn btn-secondary btn-auto">Lihat Keputusan Akhir</a>
                @elseif ($canCreateDecision)
                    <a href="{{ route('kepala-desa.keputusan-akhir.create', $calculation) }}" class="btn btn-secondary btn-auto">Tetapkan Keputusan Akhir</a>
                @else
                    <span class="badge badge-warning">Penetapan belum tersedia</span>
                @endif
            </div>
        </section>

        <section class="alert alert-success">
            Ranking pertama menunjukkan program yang paling direkomendasikan sebagai prioritas pembangunan berdasarkan kriteria dan bobot yang digunakan.
        </section>

        @if ($topResult)
            <section class="panel priority-highlight">
                <span class="badge badge-priority">Prioritas Utama</span>
                <h2>{{ $topResult->nama_program_snapshot ?? '-' }}</h2>
                <p>Jumlah Anggaran: {{ $topResult->estimasi_anggaran_snapshot !== null ? 'Rp '.number_format((float) $topResult->estimasi_anggaran_snapshot, 0, ',', '.') : '-' }}</p>
                <p>Direkomendasikan sebagai prioritas pembangunan dengan skor dominasi {{ $topResult->skor_dominasi }}.</p>
            </section>
        @endif

        @include('admin.hasil-rekomendasi._ranking-table', [
            'results' => $results,
            'calculation' => $calculation,
            'dusunPdfRouteName' => 'kepala-desa.hasil-rekomendasi.dusun-pdf',
        ])

        <section class="panel">
            <h2 class="panel-title">Detail Ringkas Perhitungan</h2>
            <div class="accordion-list">
                <details>
                    <summary>Threshold Concordance & Discordance</summary>
                    <div class="threshold-grid">
                        <div><span>Concordance</span><strong>{{ number_format((float) ($threshold['concordance'] ?? 0), 6, ',', '.') }}</strong></div>
                        <div><span>Discordance</span><strong>{{ number_format((float) ($threshold['discordance'] ?? 0), 6, ',', '.') }}</strong></div>
                    </div>
                </details>
                <details>
                    <summary>Aggregate Dominance Matrix</summary>
                    @include('admin.electre._matrix', ['matrix' => $aggregateDominance])
                </details>
            </div>
        </section>
    </div>
@endsection
