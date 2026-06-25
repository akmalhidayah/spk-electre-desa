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

        <section class="panel">
            <div class="table-wrap desktop-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Nama Periode</th>
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
                                <td colspan="6">
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

            <div class="pagination-wrap">{{ $periodes->links() }}</div>
        </section>
    </div>
@endsection
