@extends('layouts.app')

@section('title', 'Data Dusun - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Data Dusun')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Manajemen Data Dusun</h2>
                <p>Kelola alternatif dusun yang akan digunakan dalam proses perhitungan ELECTRE.</p>
            </div>
            <a href="{{ route('admin.dusuns.create') }}" class="btn btn-primary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Dusun
            </a>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.dusuns.index') }}" class="filter-bar">
                <div class="filter-field grow">
                    <label for="q" class="form-label">Pencarian</label>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        class="form-control"
                        placeholder="Cari kode alternatif atau nama dusun"
                    >
                </div>

                <div class="filter-field">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Cari
                    </button>
                    <a href="{{ route('admin.dusuns.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($dusuns->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Alternatif</th>
                                <th>Nama Dusun</th>
                                <th>Luas Tanah</th>
                                <th>Jumlah Penduduk</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dusuns as $dusun)
                                <tr>
                                    <td>{{ ($dusuns->firstItem() ?? 0) + $loop->index }}</td>
                                    <td>
                                        <span class="code-pill">{{ $dusun->kode_alternatif ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $dusun->nama_dusun }}</strong>
                                        @if ($dusun->keterangan)
                                            <small>{{ \Illuminate\Support\Str::limit($dusun->keterangan, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $dusun->luas_tanah !== null ? number_format((float) $dusun->luas_tanah, 2, ',', '.') : '-' }}</td>
                                    <td>{{ $dusun->jumlah_penduduk !== null ? number_format($dusun->jumlah_penduduk, 0, ',', '.') : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $dusun->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">
                                            {{ ucfirst($dusun->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('admin.dusuns.edit', $dusun) }}" class="btn btn-sm btn-light">Edit</a>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.dusuns.toggle-status', $dusun) }}"
                                                class="js-confirm"
                                                data-title="Ubah status dusun?"
                                                data-text="Status dusun {{ $dusun->nama_dusun }} akan diubah."
                                                data-icon="question"
                                                data-confirm-button="{{ $dusun->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    {{ $dusun->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.dusuns.destroy', $dusun) }}"
                                                class="js-confirm"
                                                data-title="Hapus data dusun?"
                                                data-text="Data akan dihapus jika belum memiliki relasi penting. Jika sudah dipakai, sistem akan meminta Anda menonaktifkannya."
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
                    @foreach ($dusuns as $dusun)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $dusun->kode_alternatif ?? '-' }}</span>
                                    <h3>{{ $dusun->nama_dusun }}</h3>
                                </div>
                                <span class="badge {{ $dusun->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">
                                    {{ ucfirst($dusun->status) }}
                                </span>
                            </div>
                            <dl class="meta-grid">
                                <div>
                                    <dt>Luas Tanah</dt>
                                    <dd>{{ $dusun->luas_tanah !== null ? number_format((float) $dusun->luas_tanah, 2, ',', '.') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt>Jumlah Penduduk</dt>
                                    <dd>{{ $dusun->jumlah_penduduk !== null ? number_format($dusun->jumlah_penduduk, 0, ',', '.') : '-' }}</dd>
                                </div>
                            </dl>
                            @if ($dusun->keterangan)
                                <p>{{ $dusun->keterangan }}</p>
                            @endif
                            <div class="mobile-actions">
                                <a href="{{ route('admin.dusuns.edit', $dusun) }}" class="btn btn-sm btn-light">Edit</a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.dusuns.toggle-status', $dusun) }}"
                                    class="js-confirm"
                                    data-title="Ubah status dusun?"
                                    data-text="Status dusun {{ $dusun->nama_dusun }} akan diubah."
                                    data-icon="question"
                                    data-confirm-button="{{ $dusun->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-secondary">
                                        {{ $dusun->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route('admin.dusuns.destroy', $dusun) }}"
                                    class="js-confirm"
                                    data-title="Hapus data dusun?"
                                    data-text="Data akan dihapus jika belum memiliki relasi penting. Jika sudah dipakai, sistem akan meminta Anda menonaktifkannya."
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
                    {{ $dusuns->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                    </div>
                    <h3>Data dusun belum ditemukan</h3>
                    <p>Tambahkan data dusun baru atau ubah filter pencarian yang sedang digunakan.</p>
                    <a href="{{ route('admin.dusuns.create') }}" class="btn btn-primary btn-auto">Tambah Dusun</a>
                </div>
            @endif
        </section>
    </div>
@endsection
