@extends('layouts.app')

@section('title', 'Data Kriteria - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Data Kriteria')

@section('content')
    @php
        $progressBobot = min($totalBobotAktif, 100);
        $bobotBadgeClass = match ($statusBobotAktif) {
            'valid' => 'badge-success',
            'lebih' => 'badge-danger',
            default => 'badge-warning',
        };
        $bobotBadgeText = match ($statusBobotAktif) {
            'valid' => 'Valid 100%',
            'lebih' => 'Melebihi 100%',
            default => 'Kurang dari 100%',
        };
    @endphp

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

        <section class="stat-grid">
            <article class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Kriteria</div>
                        <div class="stat-value">{{ number_format($totalKriteria, 0, ',', '.') }}</div>
                    </div>
                    <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg></span>
                </div>
                <div class="stat-note">Seluruh kriteria tersimpan.</div>
            </article>

            <article class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Kriteria Aktif</div>
                        <div class="stat-value">{{ number_format($totalKriteriaAktif, 0, ',', '.') }}</div>
                    </div>
                    <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg></span>
                </div>
                <div class="stat-note">Digunakan dalam perhitungan.</div>
            </article>

            <article class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Kriteria Nonaktif</div>
                        <div class="stat-value">{{ number_format($totalKriteriaNonaktif, 0, ',', '.') }}</div>
                    </div>
                    <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" /></svg></span>
                </div>
                <div class="stat-note">Tidak dihitung sementara.</div>
            </article>

            <article class="stat-card weight-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Bobot Aktif</div>
                        <div class="stat-value">{{ number_format($totalBobotAktif, 2, ',', '.') }}%</div>
                    </div>
                    <span class="badge {{ $bobotBadgeClass }}">{{ $bobotBadgeText }}</span>
                </div>
                <div class="weight-progress" aria-label="Progress total bobot aktif">
                    <span style="width: {{ $progressBobot }}%"></span>
                </div>
            </article>
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
                                        <div class="action-group">
                                            <a href="{{ route('admin.kriterias.edit', $kriteria) }}" class="btn btn-sm btn-light">Edit</a>
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
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    {{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
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
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
                                <a href="{{ route('admin.kriterias.edit', $kriteria) }}" class="btn btn-sm btn-light">Edit</a>
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
                                    <button type="submit" class="btn btn-sm btn-secondary">
                                        {{ $kriteria->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
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
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
