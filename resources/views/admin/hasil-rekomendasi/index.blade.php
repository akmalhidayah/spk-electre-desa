@extends('layouts.app')

@section('title', 'Hasil Rekomendasi - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Hasil Rekomendasi')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Hasil Rekomendasi</h2>
                <p>Riwayat hasil perhitungan ELECTRE prioritas pembangunan antar dusun.</p>
            </div>
            <a href="{{ route('admin.electre.index') }}" class="btn btn-primary btn-auto">Proses ELECTRE</a>
        </section>

        <section class="stat-grid">
            <article class="stat-card"><div class="stat-label">Total Perhitungan</div><div class="stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</div></article>
            <article class="stat-card"><div class="stat-label">Perhitungan Selesai</div><div class="stat-value">{{ number_format($stats['selesai'], 0, ',', '.') }}</div></article>
            <article class="stat-card"><div class="stat-label">Tahun Berjalan</div><div class="stat-value">{{ number_format($stats['tahun_berjalan'], 0, ',', '.') }}</div></article>
            <article class="stat-card"><div class="stat-label">Perhitungan Terbaru</div><div class="stat-value stat-value-code">{{ $stats['terbaru']?->kode_perhitungan ?? '-' }}</div></article>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.hasil-rekomendasi.index') }}" class="filter-bar usulan-filter compact-filter hasil-filter">
                <div class="filter-field grow input-with-icon compact-filter-search">
                    <label for="q" class="form-label sr-only">Pencarian</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    </span>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari kode perhitungan atau judul">
                </div>
                <div class="filter-field input-with-icon">
                    <label for="tahun" class="form-label sr-only">Tahun</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4" /><path d="M3 10h18" /><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /></svg>
                    </span>
                    <select id="tahun" name="tahun" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($tahunList as $tahun)
                            <option value="{{ $tahun }}" @selected($filters['tahun'] == $tahun)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field input-with-icon">
                    <label for="status" class="form-label sr-only">Status</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                    </span>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.hasil-rekomendasi.index') }}" class="btn btn-light">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /></svg>
                        Reset
                    </a>
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
                                <th>Alternatif</th>
                                <th>Kriteria</th>
                                <th>Status</th>
                                <th>Dihitung Oleh</th>
                                <th>Waktu</th>
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
                                    <td>{{ $calculation->total_alternatif }}</td>
                                    <td>{{ $calculation->total_kriteria }}</td>
                                    <td><span class="badge {{ $calculation->status === 'selesai' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($calculation->status) }}</span></td>
                                    <td>{{ $calculation->calculator?->name ?? '-' }}</td>
                                    <td>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('admin.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light">Lihat Hasil</a>
                                            <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation) }}" class="btn btn-sm btn-secondary" target="_blank">Cetak PDF</a>
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
                                <span class="badge {{ $calculation->status === 'selesai' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($calculation->status) }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Tahun</dt><dd>{{ $calculation->tahun }}</dd></div>
                                <div><dt>Waktu</dt><dd>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                                <div><dt>Alternatif</dt><dd>{{ $calculation->total_alternatif }}</dd></div>
                                <div><dt>Kriteria</dt><dd>{{ $calculation->total_kriteria }}</dd></div>
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('admin.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light">Lihat Hasil</a>
                                <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation) }}" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $calculations->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg></div>
                    <h3>Hasil rekomendasi belum tersedia</h3>
                    <p>Jalankan proses ELECTRE untuk membuat hasil rekomendasi prioritas pembangunan.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
