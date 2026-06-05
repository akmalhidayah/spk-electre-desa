@extends('layouts.app')

@section('title', 'Usulan Pembangunan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Usulan Pembangunan')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Usulan Pembangunan</h2>
                <p>Kelola usulan pembangunan dari masing-masing dusun.</p>
            </div>
            <a href="{{ route('admin.usulan.create') }}" class="btn btn-primary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Usulan
            </a>
        </section>

        <section class="stat-grid">
            @foreach ([
                ['label' => 'Total Usulan', 'value' => $stats['total']],
                ['label' => 'Diajukan', 'value' => $stats['diajukan']],
                ['label' => 'Diproses', 'value' => $stats['diproses']],
                ['label' => 'Diterima', 'value' => $stats['diterima']],
            ] as $stat)
                <article class="stat-card">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <div class="stat-value">{{ number_format($stat['value'], 0, ',', '.') }}</div>
                </article>
            @endforeach
        </section>

        <section class="panel stat-card-inline">
            <div>
                <div class="stat-label">Masuk Prioritas</div>
                <div class="stat-value">{{ number_format($stats['masuk_prioritas'], 0, ',', '.') }}</div>
            </div>
            <span class="badge badge-priority">Prioritas</span>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.usulan.index') }}" class="filter-bar usulan-filter">
                <div class="filter-field grow">
                    <label for="q" class="form-label">Pencarian</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari kegiatan, deskripsi, atau dusun">
                </div>
                <div class="filter-field">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select id="tahun" name="tahun" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($tahunTersedia as $tahun)
                            <option value="{{ $tahun }}" @selected($filters['tahun'] == $tahun)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="dusun_id" class="form-label">Dusun</label>
                    <select id="dusun_id" name="dusun_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($dusuns as $dusun)
                            <option value="{{ $dusun->id }}" @selected($filters['dusun_id'] == $dusun->id)>{{ $dusun->nama_dusun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.usulan.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($usulans->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun</th>
                                <th>Dusun</th>
                                <th>Nama Kegiatan</th>
                                <th>Jumlah</th>
                                <th>Estimasi Anggaran</th>
                                <th>Status</th>
                                <th>Pengaju</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usulans as $usulan)
                                <tr>
                                    <td>{{ ($usulans->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><strong>{{ $usulan->tahun }}</strong></td>
                                    <td>{{ $usulan->dusun?->nama_dusun ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $usulan->nama_kegiatan }}</strong>
                                        @if ($usulan->deskripsi)
                                            <small>{{ \Illuminate\Support\Str::limit($usulan->deskripsi, 64) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $usulan->jumlah_usulan !== null ? number_format($usulan->jumlah_usulan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</td>
                                    <td><span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span></td>
                                    <td>{{ $usulan->pengaju?->name ?? 'Admin' }}</td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('admin.usulan.edit', $usulan) }}" class="btn btn-sm btn-light">Edit</a>
                                            <a href="{{ route('admin.usulan.edit', $usulan) }}#ubah-status" class="btn btn-sm btn-secondary">Ubah Status</a>
                                            <form method="POST" action="{{ route('admin.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
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
                    @foreach ($usulans as $usulan)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $usulan->tahun }}</span>
                                    <h3>{{ $usulan->nama_kegiatan }}</h3>
                                </div>
                                <span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Dusun</dt><dd>{{ $usulan->dusun?->nama_dusun ?? '-' }}</dd></div>
                                <div><dt>Anggaran</dt><dd>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</dd></div>
                                <div><dt>Jumlah</dt><dd>{{ $usulan->jumlah_usulan !== null ? number_format($usulan->jumlah_usulan, 0, ',', '.') : '-' }}</dd></div>
                                <div><dt>Pengaju</dt><dd>{{ $usulan->pengaju?->name ?? 'Admin' }}</dd></div>
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('admin.usulan.edit', $usulan) }}" class="btn btn-sm btn-light">Edit</a>
                                <a href="{{ route('admin.usulan.edit', $usulan) }}#ubah-status" class="btn btn-sm btn-secondary">Status</a>
                                <form method="POST" action="{{ route('admin.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $usulans->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg></div>
                    <h3>Usulan pembangunan belum ditemukan</h3>
                    <p>Tambahkan usulan baru atau ubah filter pencarian.</p>
                    <a href="{{ route('admin.usulan.create') }}" class="btn btn-primary btn-auto">Tambah Usulan</a>
                </div>
            @endif
        </section>
    </div>
@endsection
