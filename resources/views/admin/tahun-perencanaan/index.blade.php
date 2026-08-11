@extends('layouts.app')

@section('title', 'Tahun Perencanaan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Tahun Perencanaan')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Tahun/Periode Perencanaan</h2>
                <p>Kelola periode RKP/RPJM dan tahun aktif sistem.</p>
            </div>
            <a href="{{ route('admin.tahun-perencanaan.create') }}" class="btn btn-primary btn-auto">Tambah Periode</a>
        </section>

        @if ($activePeriode)
            <section class="panel budget-setting-card">
                <div class="budget-setting-copy">
                    <span class="dashboard-eyebrow">Tahun Aktif</span>
                    <h2 class="panel-title">Pagu Anggaran Pembangunan Tahun {{ $activePeriode->tahun }}</h2>
                    <p class="panel-text">Atur total anggaran yang tersedia sebelum Kepala Desa menetapkan program pembangunan.</p>
                </div>
                <form method="POST" action="{{ route('admin.tahun-perencanaan.update-pagu', $activePeriode) }}" class="budget-setting-form">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label for="active_pagu_anggaran" class="form-label">Pagu Anggaran (Rp)</label>
                        <input
                            id="active_pagu_anggaran"
                            type="number"
                            name="pagu_anggaran"
                            min="0"
                            step="1"
                            value="{{ old('pagu_anggaran', $activePeriode->pagu_anggaran) }}"
                            class="form-control"
                            required
                        >
                        <small class="form-helper">Masukkan total pagu anggaran pembangunan yang tersedia pada tahun perencanaan ini.</small>
                        @error('pagu_anggaran') <small class="form-error">{{ $message }}</small> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-auto">Simpan Pagu</button>
                </form>
            </section>
        @else
            <div class="alert alert-warning">Belum ada tahun perencanaan aktif. Aktifkan salah satu tahun terlebih dahulu untuk mengatur pagu anggaran.</div>
        @endif

        <section class="panel">
            <div class="table-wrap desktop-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Nama Periode</th>
                            <th>Pagu Anggaran</th>
                            <th>Status</th>
                            <th>Hitung Ulang</th>
                            <th>Perhitungan Terakhir</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periodes as $periode)
                            <tr>
                                <td><strong>{{ $periode->tahun }}</strong></td>
                                <td>{{ $periode->nama_periode ?? '-' }}</td>
                                <td>
                                    @if ($periode->pagu_anggaran !== null)
                                        <strong>Rp {{ number_format((float) $periode->pagu_anggaran, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="badge badge-warning">Belum Diatur</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $periode->is_active ? 'badge-success' : 'badge-light' }}">{{ $periode->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    <span class="badge {{ $periode->is_locked ? 'badge-warning' : 'badge-light' }}">{{ $periode->is_locked ? 'Terkunci' : 'Terbuka' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $periode->perlu_hitung_ulang ? 'badge-warning' : 'badge-success' }}">{{ $periode->perlu_hitung_ulang ? 'Perlu Hitung Ulang' : 'Sinkron' }}</span>
                                    @if ($periode->alasan_hitung_ulang)
                                        <small>{{ $periode->alasan_hitung_ulang }}</small>
                                    @endif
                                </td>
                                <td>{{ $periode->lastElectreCalculation?->kode_perhitungan ?? '-' }}</td>
                                <td>
                                    <div class="action-group icon-actions">
                                        <a href="{{ route('admin.tahun-perencanaan.edit', $periode) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit periode" aria-label="Edit periode">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.tahun-perencanaan.set-active', $periode) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="Set tahun aktif" aria-label="Set tahun aktif" @disabled($periode->is_active)>
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20 6-11 11-5-5" /></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.tahun-perencanaan.toggle-lock', $periode) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-light action-icon-btn" title="{{ $periode->is_locked ? 'Buka kunci' : 'Kunci periode' }}" aria-label="{{ $periode->is_locked ? 'Buka kunci' : 'Kunci periode' }}">
                                                @if ($periode->is_locked)
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 9.6-2" /><path d="M5 11h14v10H5Z" /></svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" /><path d="M5 11h14v10H5Z" /></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <h3>Belum ada periode</h3>
                                        <p>Tambahkan tahun perencanaan untuk mulai memakai filter tahun aktif.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-list">
                @forelse ($periodes as $periode)
                    <article class="mobile-card">
                        <div class="mobile-card-head">
                            <div><span class="dashboard-eyebrow">Tahun Perencanaan</span><h3>{{ $periode->tahun }}</h3></div>
                            <span class="badge {{ $periode->is_active ? 'badge-success' : 'badge-light' }}">{{ $periode->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                        <p>{{ $periode->nama_periode ?? 'Nama periode belum diisi' }}</p>
                        <dl class="meta-grid">
                            <div><dt>Pagu Anggaran</dt><dd>{{ $periode->pagu_anggaran !== null ? 'Rp '.number_format((float) $periode->pagu_anggaran, 0, ',', '.') : 'Belum Diatur' }}</dd></div>
                            <div><dt>Status Periode</dt><dd>{{ $periode->is_locked ? 'Terkunci' : 'Terbuka' }}</dd></div>
                            <div><dt>Hitung Ulang</dt><dd>{{ $periode->perlu_hitung_ulang ? 'Diperlukan' : 'Sinkron' }}</dd></div>
                        </dl>
                        <div class="action-group">
                            <a href="{{ route('admin.tahun-perencanaan.edit', $periode) }}" class="btn btn-sm btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.tahun-perencanaan.set-active', $periode) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-sm btn-secondary" @disabled($periode->is_active)>Jadikan Aktif</button></form>
                            <form method="POST" action="{{ route('admin.tahun-perencanaan.toggle-lock', $periode) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-sm btn-light">{{ $periode->is_locked ? 'Buka Kunci' : 'Kunci' }}</button></form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state compact-empty"><h3>Belum ada periode</h3><p>Tambahkan tahun perencanaan untuk mulai memakai filter tahun aktif.</p></div>
                @endforelse
            </div>

            <div class="pagination-wrap">{{ $periodes->links() }}</div>
        </section>
    </div>
@endsection
