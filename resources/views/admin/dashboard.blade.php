@extends('layouts.app')

@section('title', 'Dashboard Admin - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
    <div class="stack admin-dashboard">
        <section class="dashboard-hero">
            <div>
                <h2>Selamat Datang, Admin Desa</h2>
            </div>
            <a href="{{ route('admin.electre.index') }}" class="btn btn-primary btn-auto">Proses ELECTRE</a>
        </section>

        <section class="stat-grid">
            <article class="stat-card stat-solid stat-teal">
                <div class="stat-card-row">
                    <div><div class="stat-label">Dusun Aktif</div><div class="stat-value">{{ number_format($totalDusunAktif) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-blue">
                <div class="stat-card-row">
                    <div><div class="stat-label">Kriteria Aktif</div><div class="stat-value">{{ number_format($totalKriteriaAktif) }}</div></div>
                    <span class="stat-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-amber">
                <div class="stat-card-row">
                    <div><div class="stat-label">Total Usulan</div><div class="stat-value">{{ number_format($totalUsulan) }}</div></div>
                    <span class="stat-icon icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-indigo">
                <div class="stat-card-row">
                    <div><div class="stat-label">Hasil Perhitungan</div><div class="stat-value">{{ number_format($totalPerhitungan) }}</div></div>
                    <span class="stat-icon icon-violet"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg></span>
                </div>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="panel assessment-insight">
                <div class="assessment-head">
                    <div>
                        <h2 class="panel-title">Penilaian Tahun {{ $tahunPenilaian }}</h2>
                        <p class="panel-text">{{ $totalPenilaianTerisi }} dari {{ $totalPenilaianSeharusnya }} nilai alternatif sudah terisi.</p>
                    </div>
                    <span class="badge {{ $persentasePenilaian >= 100 ? 'badge-success' : 'badge-warning' }}">{{ number_format($persentasePenilaian, 2, ',', '.') }}%</span>
                </div>

                <div class="assessment-visual">
                    <div class="assessment-ring" style="--progress: {{ min($persentasePenilaian, 100) }}%;">
                        <div>
                            <strong>{{ number_format($persentasePenilaian, 0, ',', '.') }}%</strong>
                            <span>Kelengkapan</span>
                        </div>
                    </div>
                    <div class="assessment-progress-detail">
                        <div class="assessment-progress-head">
                            <span>Progress penilaian alternatif</span>
                            <strong>{{ number_format($totalPenilaianTerisi) }}/{{ number_format($totalPenilaianSeharusnya) }}</strong>
                        </div>
                        <div class="assessment-meter">
                            <span style="width: {{ min($persentasePenilaian, 100) }}%"></span>
                        </div>
                    </div>
                </div>

                <div class="assessment-mini-grid">
                    <div><span>Terisi</span><strong>{{ number_format($totalPenilaianTerisi) }}</strong></div>
                    <div><span>Target</span><strong>{{ number_format($totalPenilaianSeharusnya) }}</strong></div>
                    <div><span>Status</span><strong>{{ $persentasePenilaian >= 100 ? 'Lengkap' : 'Belum Lengkap' }}</strong></div>
                </div>
            </article>

            <article class="panel latest-calculation-card">
                <div class="latest-calculation-head">
                    <div>
                        <span class="latest-calculation-kicker">ELECTRE</span>
                        <h2 class="panel-title">Perhitungan Terbaru</h2>
                    </div>
                    @if ($latestCalculation)
                        <span class="badge badge-info">Tahun {{ $latestCalculation->tahun }}</span>
                    @endif
                </div>

                @if ($latestCalculation)
                    <div class="latest-calculation-body">
                        <div class="latest-calculation-code">
                            <span>Kode Perhitungan</span>
                            <strong>{{ $latestCalculation->kode_perhitungan }}</strong>
                        </div>
                        <div class="latest-calculation-meta">
                            <div>
                                <span>Dihitung pada</span>
                                <strong>{{ $latestCalculation->calculated_at?->format('d/m/Y H:i') }}</strong>
                            </div>
                            <div>
                                <span>Status</span>
                                <strong>Selesai</strong>
                            </div>
                        </div>
                        <a href="{{ route('admin.hasil-rekomendasi.show', $latestCalculation) }}" class="btn btn-primary btn-auto">Lihat Hasil</a>
                    </div>
                @else
                    <div class="empty-state compact-empty">
                        <h3>Belum ada perhitungan</h3>
                        <p>Hasil ELECTRE terbaru akan tampil setelah proses perhitungan dijalankan.</p>
                    </div>
                @endif
            </article>
        </section>

        <section class="panel">
            <h2 class="panel-title">Usulan Terbaru</h2>
            @if ($latestUsulan->count() > 0)
                <div class="mini-list">
                    @foreach ($latestUsulan as $usulan)
                        <div class="mini-list-item">
                            <div>
                                <strong>{{ $usulan->nama_kegiatan }}</strong>
                                <span>{{ $usulan->dusun?->nama_dusun ?? '-' }} - {{ $usulan->tahun }}</span>
                            </div>
                            <span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state compact-empty">
                    <h3>Belum ada usulan</h3>
                    <p>Data usulan pembangunan akan tampil setelah admin atau kepala dusun menginput usulan.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
