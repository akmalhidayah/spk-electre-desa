@extends('layouts.app')

@section('title', 'Proses ELECTRE - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Proses ELECTRE')

@section('content')
    @php
        $isReady = $summary['is_ready'];
    @endphp

    <div class="stack electre-page">
        <section class="page-header-card">
            <div>
                <h2>Proses ELECTRE</h2>
                <p>Jalankan perhitungan ELECTRE berdasarkan nilai alternatif dan kriteria aktif.</p>
            </div>
            <span class="badge {{ $isReady ? 'badge-success' : 'badge-warning' }}">
                {{ $isReady ? 'Siap Diproses' : 'Belum Siap' }}
            </span>
        </section>

        <section class="panel electre-control-panel">
            <div class="electre-action-row">
                <form method="GET" action="{{ route('admin.electre.index') }}" class="filter-bar electre-year-form compact-filter electre-filter">
                    <div class="filter-field input-with-icon">
                        <label for="tahun" class="form-label sr-only">Tahun Penilaian</label>
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4" /><path d="M3 10h18" /><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /></svg>
                        </span>
                        <input id="tahun" type="number" name="tahun" min="2020" max="2100" value="{{ $tahun }}" class="form-control" required>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-secondary">
                            <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                            Cek Data
                        </button>
                    </div>
                </form>

                <form
                    method="POST"
                    action="{{ route('admin.electre.process') }}"
                    class="js-confirm"
                    data-title="Proses ELECTRE?"
                    data-text="Sistem akan membuat histori perhitungan baru untuk tahun ini."
                    data-icon="question"
                    data-confirm-button="Ya, Proses"
                >
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="btn btn-primary btn-auto" @disabled(! $isReady)>
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01" /></svg>
                        Proses ELECTRE
                    </button>
                </form>
            </div>
        </section>

        @if (! $isReady)
            <section class="alert alert-warning electre-status-alert">
                <strong>Data belum siap diproses.</strong>
                <ul>
                    @foreach ($summary['reasons'] as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </section>
        @else
            <section class="alert alert-success electre-status-alert">Data tahun {{ $tahun }} sudah siap diproses dengan metode ELECTRE.</section>
        @endif

        <section class="panel electre-history-panel">
            <div class="matrix-toolbar">
                <div>
                    <h2 class="panel-title">Riwayat Perhitungan</h2>
                </div>
                <span class="badge badge-light">{{ number_format($histories->total(), 0, ',', '.') }} Riwayat</span>
            </div>

            @if ($histories->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table electre-history-table">
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
                            @foreach ($histories as $history)
                                <tr>
                                    <td>{{ ($histories->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><span class="code-pill">{{ $history->kode_perhitungan }}</span></td>
                                    <td>{{ $history->tahun }}</td>
                                    <td><strong>{{ $history->judul ?? '-' }}</strong></td>
                                    <td>{{ $history->total_alternatif }}</td>
                                    <td>{{ $history->total_kriteria }}</td>
                                    <td><span class="badge badge-success">{{ ucfirst($history->status) }}</span></td>
                                    <td>{{ $history->calculator?->name ?? '-' }}</td>
                                    <td>{{ $history->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="action-group icon-actions">
                                            <a href="{{ route('admin.electre.show', $history) }}" class="btn btn-sm btn-light action-icon-btn" title="Lihat hasil" aria-label="Lihat hasil">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.electre.destroy', $history) }}" class="js-confirm" data-title="Hapus Histori?" data-text="Histori perhitungan dan detail hasil akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus histori" aria-label="Hapus histori">
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
                    @foreach ($histories as $history)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $history->kode_perhitungan }}</span>
                                    <h3>{{ $history->judul ?? 'Perhitungan ELECTRE' }}</h3>
                                </div>
                                <span class="badge badge-success">{{ ucfirst($history->status) }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Tahun</dt><dd>{{ $history->tahun }}</dd></div>
                                <div><dt>Waktu</dt><dd>{{ $history->calculated_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                                <div><dt>Alternatif</dt><dd>{{ $history->total_alternatif }}</dd></div>
                                <div><dt>Kriteria</dt><dd>{{ $history->total_kriteria }}</dd></div>
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('admin.electre.show', $history) }}" class="btn btn-sm btn-light action-icon-btn" title="Lihat hasil" aria-label="Lihat hasil">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.electre.destroy', $history) }}" class="js-confirm" data-title="Hapus Histori?" data-text="Histori perhitungan dan detail hasil akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus histori" aria-label="Hapus histori">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $histories->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01" /></svg></div>
                    <h3>Belum ada histori perhitungan</h3>
                    <p>Jalankan proses ELECTRE setelah penilaian alternatif lengkap.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
