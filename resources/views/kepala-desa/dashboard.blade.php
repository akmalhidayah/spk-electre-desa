@extends('layouts.app')

@section('title', 'Dashboard Kepala Desa - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa')
@section('page-title', 'Dashboard Kepala Desa')

@section('content')
    <div class="stack kepala-desa-dashboard role-dashboard dashboard-shell">
        <section class="dashboard-hero dashboard-welcome">
            <div class="dashboard-welcome-copy">
                <span class="dashboard-eyebrow">Ringkasan Eksekutif</span>
                <h2>Selamat Datang, Kepala Desa</h2>
                <p>Pantau rekomendasi prioritas, penggunaan pagu, dan keputusan pembangunan desa secara ringkas.</p>
            </div>
            <div class="dashboard-welcome-actions">
                <a href="{{ route('kepala-desa.keputusan-akhir.index') }}" class="btn btn-secondary btn-auto">Keputusan Akhir</a>
                <a href="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="btn btn-primary btn-auto">Hasil Rekomendasi</a>
            </div>
        </section>

        @php
            $paguTersedia = $budgetSummary['pagu'] !== null;
            $persentaseAlokasi = min((float) ($budgetSummary['persentase_alokasi'] ?? 0), 100);
        @endphp

        <section class="budget-overview budget-overview-executive">
            <div class="budget-overview-head">
                <div><span class="dashboard-eyebrow">Anggaran Pembangunan</span><h2>Tahun Perencanaan {{ $tahun }}</h2></div>
                <span class="badge {{ $paguTersedia ? 'badge-success' : 'badge-warning' }}">{{ $paguTersedia ? number_format($persentaseAlokasi, 1, ',', '.') . '% dialokasikan' : 'Pagu belum diatur' }}</span>
            </div>
            <div class="budget-overview-body">
                <div class="budget-balance">
                    <span>Sisa pagu tersedia</span>
                    <strong>{{ $paguTersedia ? 'Rp '.number_format($budgetSummary['sisa_pagu'], 0, ',', '.') : 'Belum diatur' }}</strong>
                    <div class="budget-progress"><span style="width: {{ $persentaseAlokasi }}%"></span></div>
                    <small>{{ $paguTersedia ? number_format($budgetSummary['jumlah_program_ditetapkan']) . ' program telah masuk keputusan akhir.' : 'Pagu dapat diatur melalui data tahun perencanaan oleh admin.' }}</small>
                </div>
                <div class="budget-metrics">
                    <div class="budget-metric"><span>Total pagu</span><strong>{{ $paguTersedia ? 'Rp '.number_format($budgetSummary['pagu'], 0, ',', '.') : '-' }}</strong></div>
                    <div class="budget-metric"><span>Sudah ditetapkan</span><strong>Rp {{ number_format($budgetSummary['total_ditetapkan'], 0, ',', '.') }}</strong></div>
                    <div class="budget-metric"><span>Program terpilih</span><strong>{{ number_format($budgetSummary['jumlah_program_ditetapkan']) }} program</strong></div>
                </div>
            </div>
        </section>

        <section class="stat-grid dashboard-kpi-grid">
            <article class="stat-card stat-solid stat-indigo">
                <div class="stat-card-row">
                    <div><div class="stat-label">Hasil Rekomendasi</div><div class="stat-value">{{ number_format($totalSelesai) }}</div></div>
                    <span class="stat-icon icon-violet"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-teal">
                <div class="stat-card-row">
                    <div><div class="stat-label">Dusun Aktif</div><div class="stat-value">{{ number_format($totalDusunAktif) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-amber">
                <div class="stat-card-row">
                    <div><div class="stat-label">Prioritas Utama Terbaru</div><div class="stat-value stat-value-code">{{ $prioritasUtamaTerbaru?->nama_program_snapshot ?? '-' }}</div></div>
                    <span class="stat-icon icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1-4.4-4.3 6.1-.9Z" /></svg></span>
                </div>
            </article>
            <article class="stat-card stat-solid stat-blue">
                <div class="stat-card-row">
                    <div><div class="stat-label">Perhitungan Terakhir</div><div class="stat-value stat-value-code">{{ $perhitunganTerakhir?->kode_perhitungan ?? '-' }}</div></div>
                    <span class="stat-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01" /></svg></span>
                </div>
            </article>
        </section>

        <section class="dashboard-grid kepala-desa-main-grid">
            <article class="panel latest-recommendation-card">
                <div class="latest-recommendation-head">
                    <div>
                        <span class="latest-recommendation-kicker">ELECTRE</span>
                        <h2 class="panel-title">Rekomendasi Terbaru</h2>
                    </div>
                    @if ($perhitunganTerakhir)
                        <span class="badge badge-info">Tahun {{ $perhitunganTerakhir->tahun }}</span>
                    @endif
                </div>

                @if ($perhitunganTerakhir)
                    <div class="latest-recommendation-body">
                        <div>
                            <span>Perhitungan</span>
                            <strong>{{ $perhitunganTerakhir->judul ?? $perhitunganTerakhir->kode_perhitungan }}</strong>
                        </div>
                        <div>
                            <span>Waktu</span>
                            <strong>{{ $perhitunganTerakhir->calculated_at?->format('d/m/Y H:i') }}</strong>
                        </div>
                        <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $perhitunganTerakhir) }}" class="btn btn-primary btn-auto">Lihat Detail</a>
                    </div>
                @else
                    <div class="empty-state compact-empty">
                        <h3>Belum ada rekomendasi</h3>
                        <p>Hasil rekomendasi akan tampil setelah perhitungan ELECTRE selesai.</p>
                    </div>
                @endif
            </article>
        </section>
    </div>
@endsection
