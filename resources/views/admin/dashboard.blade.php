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

        <section class="stat-grid">
            <article class="stat-card stat-solid stat-slate">
                <div class="stat-card-row">
                    <div><div class="stat-label">Total User</div><div class="stat-value">{{ number_format($totalUser) }}</div></div>
                    <span class="stat-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-green">
                <div class="stat-card-row">
                    <div><div class="stat-label">User Aktif</div><div class="stat-value">{{ number_format($totalUserAktif) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20 6-11 11-5-5" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-purple">
                <div class="stat-card-row">
                    <div><div class="stat-label">Admin</div><div class="stat-value">{{ number_format($totalAdmin) }}</div></div>
                    <span class="stat-icon icon-violet"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v6c0 5 3.4 7.4 8 8 4.6-.6 8-3 8-8V7Z" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-orange">
                <div class="stat-card-row">
                    <div><div class="stat-label">Kepala Desa</div><div class="stat-value">{{ number_format($totalKepalaDesa) }}</div></div>
                    <span class="stat-icon icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18" /><path d="M5 21V8l7-5 7 5v13" /><path d="M9 21v-7h6v7" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-cyan">
                <div class="stat-card-row">
                    <div><div class="stat-label">Kepala Dusun</div><div class="stat-value">{{ number_format($totalKepalaDusun) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg></span>
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

                <div class="assessment-chart">
                    <svg viewBox="0 0 520 150" role="img" aria-label="Grafik kelengkapan penilaian">
                        <path class="chart-grid" d="M20 120H500M20 85H500M20 50H500" />
                        <path class="chart-curve" d="M22 118 C95 112 122 76 188 82 C254 88 279 42 344 52 C407 61 438 34 498 26" />
                        <circle cx="498" cy="26" r="7" class="chart-point" />
                    </svg>
                    <div class="assessment-meter">
                        <span style="width: {{ min($persentasePenilaian, 100) }}%"></span>
                    </div>
                </div>

                <div class="assessment-mini-grid">
                    <div><span>Terisi</span><strong>{{ number_format($totalPenilaianTerisi) }}</strong></div>
                    <div><span>Target</span><strong>{{ number_format($totalPenilaianSeharusnya) }}</strong></div>
                    <div><span>Status</span><strong>{{ $persentasePenilaian >= 100 ? 'Lengkap' : 'Belum Lengkap' }}</strong></div>
                </div>
            </article>

            <article class="panel">
                <h2 class="panel-title">Perhitungan Terbaru</h2>
                @if ($latestCalculation)
                    <p class="panel-text"><strong>{{ $latestCalculation->kode_perhitungan }}</strong><br>Tahun {{ $latestCalculation->tahun }} - {{ $latestCalculation->calculated_at?->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('admin.hasil-rekomendasi.show', $latestCalculation) }}" class="btn btn-light btn-auto">Lihat Hasil</a>
                @else
                    <p class="panel-text">Belum ada histori perhitungan ELECTRE.</p>
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
