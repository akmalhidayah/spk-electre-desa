@extends('layouts.app')

@section('title', 'Riwayat Usulan - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Dusun')
@section('page-title', 'Riwayat Usulan Pembangunan')

@section('content')
    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Riwayat Usulan Pembangunan</h2>
                <p>Daftar usulan pembangunan yang diajukan oleh dusun Anda.</p>
            </div>
            <a href="{{ route('kepala-dusun.usulan.create') }}" class="btn btn-primary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Ajukan Usulan
            </a>
        </section>

        @if (! $dusun)
            <div class="alert alert-warning">Akun Anda belum terhubung dengan data dusun. Silakan hubungi admin.</div>
        @endif

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
            <span class="badge badge-priority">{{ $dusun?->nama_dusun ?? 'Belum Ada Dusun' }}</span>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('kepala-dusun.usulan.index') }}" class="filter-bar filter-bar-extended">
                <div class="filter-field grow">
                    <label for="q" class="form-label">Pencarian</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari nama kegiatan atau deskripsi">
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
                    <a href="{{ route('kepala-dusun.usulan.index') }}" class="btn btn-light">Reset</a>
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
                                <th>Nama Kegiatan</th>
                                <th>Jumlah</th>
                                <th>Estimasi Anggaran</th>
                                <th>Status</th>
                                <th>Catatan Admin</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usulans as $usulan)
                                <tr>
                                    <td>{{ ($usulans->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><strong>{{ $usulan->tahun }}</strong></td>
                                    <td>
                                        <strong>{{ $usulan->nama_kegiatan }}</strong>
                                        @if ($usulan->deskripsi)
                                            <small>{{ \Illuminate\Support\Str::limit($usulan->deskripsi, 70) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $usulan->jumlah_usulan !== null ? number_format($usulan->jumlah_usulan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</td>
                                    <td><span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span></td>
                                    <td>{{ $usulan->catatan_admin ? \Illuminate\Support\Str::limit($usulan->catatan_admin, 50) : '-' }}</td>
                                    <td>
                                        @if ($usulan->status === \App\Models\UsulanPembangunan::STATUS_DIAJUKAN)
                                            <div class="action-group">
                                                <a href="{{ route('kepala-dusun.usulan.edit', $usulan) }}" class="btn btn-sm btn-light">Edit</a>
                                                <form method="POST" action="{{ route('kepala-dusun.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge badge-muted">Terkunci</span>
                                        @endif
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
                                <div><dt>Jumlah</dt><dd>{{ $usulan->jumlah_usulan !== null ? number_format($usulan->jumlah_usulan, 0, ',', '.') : '-' }}</dd></div>
                                <div><dt>Anggaran</dt><dd>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</dd></div>
                            </dl>
                            @if ($usulan->catatan_admin)
                                <p><strong>Catatan:</strong> {{ $usulan->catatan_admin }}</p>
                            @endif
                            <div class="mobile-actions">
                                @if ($usulan->status === \App\Models\UsulanPembangunan::STATUS_DIAJUKAN)
                                    <a href="{{ route('kepala-dusun.usulan.edit', $usulan) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form method="POST" action="{{ route('kepala-dusun.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @else
                                    <span class="badge badge-muted">Terkunci</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $usulans->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg></div>
                    <h3>Belum ada usulan pembangunan</h3>
                    <p>Ajukan kebutuhan pembangunan dusun Anda agar dapat ditinjau admin.</p>
                    @if ($dusun)
                        <a href="{{ route('kepala-dusun.usulan.create') }}" class="btn btn-primary btn-auto">Ajukan Usulan</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
