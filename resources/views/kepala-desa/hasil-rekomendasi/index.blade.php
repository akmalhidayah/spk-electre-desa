@extends('layouts.app')

@section('title', 'Hasil Rekomendasi Prioritas - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa')
@section('page-title', 'Hasil Rekomendasi Prioritas')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Hasil Rekomendasi Prioritas</h2>
                <p>Lihat hasil rekomendasi prioritas pembangunan berdasarkan metode ELECTRE.</p>
            </div>
        </section>

        <section class="stat-grid">
            <article class="stat-card"><div class="stat-label">Total Hasil Rekomendasi</div><div class="stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</div></article>
            <article class="stat-card"><div class="stat-label">Perhitungan Terbaru</div><div class="stat-value stat-value-code">{{ $stats['terbaru']?->kode_perhitungan ?? '-' }}</div></article>
            <article class="stat-card"><div class="stat-label">Tahun Berjalan</div><div class="stat-value">{{ number_format($stats['tahun_berjalan'], 0, ',', '.') }}</div></article>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="filter-bar filter-bar-extended">
                <div class="filter-field grow">
                    <label for="q" class="form-label">Pencarian</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari kode perhitungan atau judul">
                </div>
                <div class="filter-field">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select id="tahun" name="tahun" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($tahunList as $tahun)
                            <option value="{{ $tahun }}" @selected($filters['tahun'] == $tahun)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($calculations->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Perhitungan</th>
                                <th>Tahun</th>
                                <th>Judul</th>
                                <th>Waktu Perhitungan</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($calculations as $calculation)
                                <tr>
                                    <td>{{ ($calculations->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><span class="code-pill">{{ $calculation->kode_perhitungan }}</span></td>
                                    <td>{{ $calculation->tahun }}</td>
                                    <td><strong>{{ $calculation->judul ?? '-' }}</strong></td>
                                    <td>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light">Lihat Rekomendasi</a>
                                            <a href="{{ route('kepala-desa.hasil-rekomendasi.pdf', $calculation) }}" class="btn btn-sm btn-secondary" target="_blank">Cetak Laporan</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach ($calculations as $calculation)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $calculation->kode_perhitungan }}</span>
                                    <h3>{{ $calculation->judul ?? 'Hasil ELECTRE' }}</h3>
                                </div>
                                <span class="badge badge-success">Selesai</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Tahun</dt><dd>{{ $calculation->tahun }}</dd></div>
                                <div><dt>Waktu</dt><dd>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light">Lihat</a>
                                <a href="{{ route('kepala-desa.hasil-rekomendasi.pdf', $calculation) }}" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $calculations->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg></div>
                    <h3>Hasil rekomendasi belum tersedia</h3>
                    <p>Hasil rekomendasi akan tampil setelah admin menyelesaikan proses ELECTRE.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
