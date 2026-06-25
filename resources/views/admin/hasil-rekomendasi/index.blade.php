@extends('layouts.app')

@section('title', 'Hasil Rekomendasi - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Hasil Rekomendasi')

@section('content')
    <div class="stack hasil-rekomendasi-page">
        <section class="page-header-card">
            <div>
                <h2>Hasil Rekomendasi</h2>
                <p>Riwayat hasil perhitungan ELECTRE prioritas pembangunan antar dusun.</p>
            </div>
            <a href="{{ route('admin.electre.index') }}" class="btn btn-primary btn-auto">Proses ELECTRE</a>
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
                                <th>Status</th>
                                <th>Waktu</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($calculations as $calculation)
                                @php
                                    $keputusan = $calculation->keputusanAkhir;
                                    $isDitetapkan = $keputusan?->status === \App\Models\KeputusanAkhir::STATUS_DITETAPKAN;
                                    $statusLabel = $isDitetapkan ? 'Ditetapkan' : ($keputusan ? 'Draft' : 'Belum Ditetapkan');
                                    $statusClass = $isDitetapkan ? 'badge-success' : ($keputusan ? 'badge-warning' : 'badge-muted');
                                    $displayTitle = 'Perhitungan Pembangunan Usulan Tahun '.$calculation->tahun;
                                @endphp
                                <tr>
                                    <td>{{ ($calculations->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><span class="code-pill">{{ $calculation->kode_perhitungan }}</span></td>
                                    <td>{{ $calculation->tahun }}</td>
                                    <td><strong>{{ $displayTitle }}</strong></td>
                                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="action-group icon-actions">
                                            <a href="{{ route('admin.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light action-icon-btn" title="Lihat hasil" aria-label="Lihat hasil">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                                            </a>
                                            <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation->tahun) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF" aria-label="Cetak PDF">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                                            </a>
                                            @if ($keputusan)
                                                <a href="{{ route('admin.hasil-rekomendasi.keputusan-pdf', $calculation) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF keputusan akhir" aria-label="Cetak PDF keputusan akhir">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6" /><path d="M8 14h8M8 17h5" /></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach ($calculations as $calculation)
                        @php
                            $keputusan = $calculation->keputusanAkhir;
                            $isDitetapkan = $keputusan?->status === \App\Models\KeputusanAkhir::STATUS_DITETAPKAN;
                            $statusLabel = $isDitetapkan ? 'Ditetapkan' : ($keputusan ? 'Draft' : 'Belum Ditetapkan');
                            $statusClass = $isDitetapkan ? 'badge-success' : ($keputusan ? 'badge-warning' : 'badge-muted');
                            $displayTitle = 'Perhitungan Pembangunan Usulan Tahun '.$calculation->tahun;
                        @endphp
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $calculation->kode_perhitungan }}</span>
                                    <h3>{{ $displayTitle }}</h3>
                                </div>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Tahun</dt><dd>{{ $calculation->tahun }}</dd></div>
                                <div><dt>Waktu</dt><dd>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('admin.hasil-rekomendasi.show', $calculation) }}" class="btn btn-sm btn-light action-icon-btn" title="Lihat hasil" aria-label="Lihat hasil">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                                </a>
                                <a href="{{ route('admin.hasil-rekomendasi.pdf', $calculation->tahun) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF" aria-label="Cetak PDF">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                                </a>
                                @if ($keputusan)
                                    <a href="{{ route('admin.hasil-rekomendasi.keputusan-pdf', $calculation) }}" class="btn btn-sm btn-secondary action-icon-btn" target="_blank" title="Cetak PDF keputusan akhir" aria-label="Cetak PDF keputusan akhir">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6" /><path d="M8 14h8M8 17h5" /></svg>
                                    </a>
                                @endif
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
