@extends('layouts.app')

@section('title', 'Data Kriteria - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Data Kriteria')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Data Kriteria</h2>
                <p>Kelola kriteria dan bobot penilaian metode ELECTRE.</p>
            </div>
            <a href="{{ route('admin.kriterias.create') }}" class="btn btn-primary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Kriteria
            </a>
        </section>

        @if ($statusBobotAktif === 'kurang')
            <div class="alert alert-warning">Total bobot aktif belum mencapai 100%. Perhitungan ELECTRE belum ideal.</div>
        @elseif ($statusBobotAktif === 'valid')
            <div class="alert alert-success">Total bobot aktif sudah valid.</div>
        @else
            <div class="alert alert-danger">Total bobot aktif melebihi 100%. Silakan periksa data kriteria aktif.</div>
        @endif

        <section class="panel">
            <form method="GET" action="{{ route('admin.kriterias.index') }}" class="filter-bar filter-bar-extended compact-filter kriteria-filter">
                <div class="filter-field grow input-with-icon compact-filter-search">
                    <label for="q" class="form-label sr-only">Pencarian</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    </span>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        class="form-control"
                        placeholder="Cari kode atau nama kriteria"
                    >
                </div>

                <div class="filter-field input-with-icon compact-filter-status">
                    <label for="status" class="form-label sr-only">Status</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                    </span>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>

                <div class="filter-field input-with-icon compact-filter-type">
                    <label for="tipe" class="form-label sr-only">Tipe</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16" /><path d="M7 12h10" /><path d="M10 17h4" /></svg>
                    </span>
                    <select id="tipe" name="tipe" class="form-control">
                        <option value="">Semua</option>
                        <option value="benefit" @selected($filters['tipe'] === 'benefit')>Benefit</option>
                        <option value="cost" @selected($filters['tipe'] === 'cost')>Cost</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Cari
                    </button>
                    <a href="{{ route('admin.kriterias.index') }}" class="btn btn-light">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /></svg>
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($kriterias->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot</th>
                                <th>Tipe</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kriterias as $kriteria)
                                <tr>
                                    <td>{{ ($kriterias->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><span class="code-pill">{{ $kriteria->kode }}</span></td>
                                    <td>
                                        <strong>{{ $kriteria->nama_kriteria }}</strong>
                                        @if ($kriteria->deskripsi)
                                            <small>{{ \Illuminate\Support\Str::limit($kriteria->deskripsi, 70) }}</small>
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format((float) $kriteria->bobot, 2, ',', '.') }}%</strong></td>
                                    <td><span class="badge badge-info">{{ ucfirst($kriteria->tipe) }}</span></td>
                                    <td>{{ $kriteria->urutan }}</td>
                                    <td>
                                        <span class="badge {{ $kriteria->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">
                                            {{ ucfirst($kriteria->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group icon-actions">
                                            <a href="{{ route('admin.kriterias.edit', $kriteria) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit kriteria" aria-label="Edit kriteria">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.kriterias.toggle-status', $kriteria) }}"
                                                class="js-confirm"
                                                data-title="Ubah Status Kriteria?"
                                                data-text="Status kriteria akan diubah. Lanjutkan?"
                                                data-icon="warning"
                                                data-confirm-button="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10" /><path d="M18.4 6.6a9 9 0 1 1-12.8 0" /></svg>
                                                </button>
                                            </form>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.kriterias.destroy', $kriteria) }}"
                                                class="js-confirm"
                                                data-title="Hapus Kriteria?"
                                                data-text="Data yang sudah digunakan dalam penilaian tidak dapat dihapus."
                                                data-icon="warning"
                                                data-confirm-button="Ya, hapus"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus kriteria" aria-label="Hapus kriteria">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach ($kriterias as $kriteria)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $kriteria->kode }}</span>
                                    <h3>{{ $kriteria->nama_kriteria }}</h3>
                                </div>
                                <span class="badge {{ $kriteria->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">
                                    {{ ucfirst($kriteria->status) }}
                                </span>
                            </div>
                            <dl class="meta-grid">
                                <div>
                                    <dt>Bobot</dt>
                                    <dd>{{ number_format((float) $kriteria->bobot, 2, ',', '.') }}%</dd>
                                </div>
                                <div>
                                    <dt>Tipe</dt>
                                    <dd>{{ ucfirst($kriteria->tipe) }}</dd>
                                </div>
                                <div>
                                    <dt>Urutan</dt>
                                    <dd>{{ $kriteria->urutan }}</dd>
                                </div>
                            </dl>
                            @if ($kriteria->deskripsi)
                                <p>{{ $kriteria->deskripsi }}</p>
                            @endif
                            <div class="mobile-actions">
                                <a href="{{ route('admin.kriterias.edit', $kriteria) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit kriteria" aria-label="Edit kriteria">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.kriterias.toggle-status', $kriteria) }}"
                                    class="js-confirm"
                                    data-title="Ubah Status Kriteria?"
                                    data-text="Status kriteria akan diubah. Lanjutkan?"
                                    data-icon="warning"
                                    data-confirm-button="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10" /><path d="M18.4 6.6a9 9 0 1 1-12.8 0" /></svg>
                                    </button>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route('admin.kriterias.destroy', $kriteria) }}"
                                    class="js-confirm"
                                    data-title="Hapus Kriteria?"
                                    data-text="Data yang sudah digunakan dalam penilaian tidak dapat dihapus."
                                    data-icon="warning"
                                    data-confirm-button="Ya, hapus"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus kriteria" aria-label="Hapus kriteria">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">
                    {{ $kriterias->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg>
                    </div>
                    <h3>Data kriteria belum ditemukan</h3>
                    <p>Tambahkan kriteria baru atau ubah filter pencarian yang sedang digunakan.</p>
                    <a href="{{ route('admin.kriterias.create') }}" class="btn btn-primary btn-auto">Tambah Kriteria</a>
                </div>
            @endif
        </section>
    </div>
@endsection
