@extends('layouts.app')

@section('title', 'Detail Keputusan Akhir - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa / Keputusan Akhir')
@section('page-title', 'Detail Keputusan Akhir')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Detail Keputusan Akhir</h2>
                <p>{{ $calculation?->kode_perhitungan ?? '-' }}</p>
            </div>
            <div class="form-actions">
                <a href="{{ route('kepala-desa.keputusan-akhir.pdf', $keputusan) }}" class="btn btn-primary btn-auto" target="_blank">Cetak PDF Penetapan</a>
                @if ($calculation)
                    <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-light">Kembali</a>
                @endif
            </div>
        </section>

        <section class="panel priority-highlight">
            <span class="badge badge-success">{{ ucfirst($keputusan->status) }}</span>
            <h2>{{ $keputusan->dusun?->nama_dusun ?? 'Dusun belum dipilih' }}</h2>
            <p>{{ $keputusan->dasar_pertimbangan ?? 'Dasar pertimbangan belum diisi.' }}</p>
            <dl class="meta-grid">
                <div><dt>Nomor Keputusan</dt><dd>{{ $keputusan->nomor_keputusan ?? '-' }}</dd></div>
                <div><dt>Tanggal</dt><dd>{{ $keputusan->tanggal_keputusan?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt>Tahun</dt><dd>{{ $keputusan->tahun ?? '-' }}</dd></div>
                <div><dt>Ditetapkan Oleh</dt><dd>{{ $keputusan->penetap?->name ?? $keputusan->decider?->name ?? '-' }}</dd></div>
            </dl>
            @if ($keputusan->catatan_keputusan)
                <p>{{ $keputusan->catatan_keputusan }}</p>
            @endif
        </section>

        @include('admin.hasil-rekomendasi._ranking-table', ['results' => $results])
    </div>
@endsection
