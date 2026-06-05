@extends('layouts.app')

@section('title', 'Tetapkan Keputusan Akhir - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa / Keputusan Akhir')
@section('page-title', 'Tetapkan Keputusan Akhir')

@section('content')
    @php
        $topResult = $results->first();
    @endphp

    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Tetapkan Keputusan Akhir</h2>
                <p>Halaman ini disiapkan dari hasil rekomendasi {{ $calculation->kode_perhitungan }}.</p>
            </div>
            <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="alert alert-success">
            Pilih dusun yang akan ditetapkan sebagai prioritas pembangunan. Ranking pertama merupakan rekomendasi utama dari sistem, namun keputusan akhir tetap dapat disesuaikan dengan hasil musyawarah desa.
        </section>

        @if ($topResult)
            <section class="panel priority-highlight">
                <span class="badge badge-priority">Rekomendasi Utama Sistem</span>
                <h2>{{ $topResult->dusun?->nama_dusun ?? '-' }}</h2>
                <p>Ranking {{ $topResult->ranking }} dengan skor dominasi {{ $topResult->skor_dominasi }}.</p>
            </section>
        @endif

        @include('admin.hasil-rekomendasi._ranking-table', ['results' => $results])

        <section class="panel">
            <h2 class="panel-title">Form Keputusan Akhir</h2>
            <p class="panel-text">Isi data keputusan pemerintah desa berdasarkan rekomendasi ELECTRE dan hasil pertimbangan musyawarah.</p>

            @include('kepala-desa.keputusan-akhir._form', [
                'calculation' => $calculation,
                'results' => $results,
            ])
        </section>
    </div>
@endsection
