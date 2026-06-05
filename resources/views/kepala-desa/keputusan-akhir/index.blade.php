@extends('layouts.app')

@section('title', 'Laporan Keputusan Akhir - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa / Keputusan Akhir')
@section('page-title', 'Laporan Keputusan Akhir')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Laporan Keputusan Akhir</h2>
                <p>Daftar keputusan prioritas pembangunan yang sudah disimpan oleh kepala desa.</p>
            </div>
            <a href="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="btn btn-primary btn-auto">Buat dari Rekomendasi</a>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Keputusan</span>
                <strong>{{ $totalKeputusan }}</strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Draft</span>
                <strong>{{ $totalDraft }}</strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Ditetapkan</span>
                <strong>{{ $totalDitetapkan }}</strong>
            </div>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('kepala-desa.keputusan-akhir.index') }}" class="filter-grid">
                <div class="form-group">
                    <label for="q" class="form-label">Pencarian</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nomor, kode, judul, atau dusun">
                </div>
                <div class="form-group">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input id="tahun" type="number" name="tahun" value="{{ request('tahun') }}" class="form-control" min="2020" max="2100" placeholder="Semua">
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="ditetapkan" @selected(request('status') === 'ditetapkan')>Ditetapkan</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-auto">Filter</button>
                    <a href="{{ route('kepala-desa.keputusan-akhir.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="table-responsive desktop-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Keputusan</th>
                            <th>Tahun</th>
                            <th>Dusun Terpilih</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Perhitungan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keputusans as $keputusan)
                            <tr>
                                <td>{{ $keputusans->firstItem() + $loop->index }}</td>
                                <td>{{ $keputusan->nomor_keputusan ?: '-' }}</td>
                                <td>{{ $keputusan->tahun ?: ($keputusan->calculation?->tahun ?? '-') }}</td>
                                <td><strong>{{ $keputusan->dusun?->nama_dusun ?? '-' }}</strong></td>
                                <td>
                                    <span class="badge {{ $keputusan->status === 'ditetapkan' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($keputusan->status) }}
                                    </span>
                                </td>
                                <td>{{ $keputusan->tanggal_keputusan?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $keputusan->calculation?->kode_perhitungan ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('kepala-desa.keputusan-akhir.show', $keputusan) }}" class="btn btn-light btn-sm">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <strong>Belum ada keputusan akhir.</strong>
                                        <p>Buka hasil rekomendasi, lalu tetapkan keputusan akhir dari perhitungan yang sudah selesai.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-card-list">
                @forelse ($keputusans as $keputusan)
                    <article class="mobile-card">
                        <div class="mobile-card-head">
                            <span class="badge {{ $keputusan->status === 'ditetapkan' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($keputusan->status) }}</span>
                            <span>{{ $keputusan->tanggal_keputusan?->format('d/m/Y') ?? '-' }}</span>
                        </div>
                        <h3>{{ $keputusan->dusun?->nama_dusun ?? '-' }}</h3>
                        <p>{{ $keputusan->nomor_keputusan ?: 'Nomor keputusan belum diisi' }}</p>
                        <p>Tahun {{ $keputusan->tahun ?: ($keputusan->calculation?->tahun ?? '-') }} · {{ $keputusan->calculation?->kode_perhitungan ?? '-' }}</p>
                        <a href="{{ route('kepala-desa.keputusan-akhir.show', $keputusan) }}" class="btn btn-light">Lihat Keputusan</a>
                    </article>
                @empty
                    <div class="empty-state">
                        <strong>Belum ada keputusan akhir.</strong>
                        <p>Buka hasil rekomendasi, lalu tetapkan keputusan akhir dari perhitungan yang sudah selesai.</p>
                    </div>
                @endforelse
            </div>

            {{ $keputusans->links() }}
        </section>
    </div>
@endsection
