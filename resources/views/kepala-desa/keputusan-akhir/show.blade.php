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
            <h2>{{ $selectedDetails->count() }} Program Ditetapkan</h2>
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

        <section class="panel">
            <h2 class="panel-title">Program yang Ditetapkan</h2>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>No</th><th>Ranking</th><th>Kode</th><th>Program</th><th>Jumlah Anggaran</th></tr></thead><tbody>
                @foreach ($selectedDetails as $detail)<tr><td>{{ $loop->iteration }}</td><td>{{ $detail->ranking_snapshot ?? $detail->ranking ?? '-' }}</td><td>{{ $detail->kode_alternatif_snapshot ?? $detail->kode_alternatif ?? '-' }}</td><td>{{ $detail->nama_program_snapshot ?? '-' }}</td><td>{{ ($detail->estimasi_anggaran_snapshot ?? $detail->program?->estimasi_anggaran ?? null) !== null ? 'Rp '.number_format((float) ($detail->estimasi_anggaran_snapshot ?? $detail->program?->estimasi_anggaran), 0, ',', '.') : '-' }}</td></tr>@endforeach
            </tbody></table></div>
            <p><strong>Total Anggaran Keputusan Ini:</strong> Rp {{ number_format((float) $selectedDetails->sum(fn ($item) => $item->estimasi_anggaran_snapshot ?? 0), 0, ',', '.') }}</p>
        </section>

        @if ($budgetSummary)
            <section class="panel"><h2 class="panel-title">Ringkasan Anggaran Saat Penetapan</h2><dl class="meta-grid"><div><dt>Pagu</dt><dd>{{ isset($budgetSummary['pagu_anggaran']) ? 'Rp '.number_format((float) $budgetSummary['pagu_anggaran'], 0, ',', '.') : '-' }}</dd></div><div><dt>Penetapan Ini</dt><dd>Rp {{ number_format((float) ($budgetSummary['total_keputusan_ini'] ?? 0), 0, ',', '.') }}</dd></div><div><dt>Total Setelah Penetapan</dt><dd>Rp {{ number_format((float) ($budgetSummary['total_ditetapkan_setelah_keputusan'] ?? 0), 0, ',', '.') }}</dd></div><div><dt>Sisa Pagu</dt><dd>Rp {{ number_format((float) ($budgetSummary['sisa_pagu_setelah_keputusan'] ?? 0), 0, ',', '.') }}</dd></div></dl></section>
        @endif

        @include('admin.hasil-rekomendasi._ranking-table', ['results' => $results])
    </div>
@endsection
