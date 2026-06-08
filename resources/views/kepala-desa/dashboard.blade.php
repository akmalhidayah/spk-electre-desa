@extends('layouts.app')

@section('title', 'Dashboard Kepala Desa - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa')
@section('page-title', 'Dashboard Kepala Desa')

@section('content')
    <div class="stack kepala-desa-dashboard role-dashboard">
        <section class="dashboard-hero">
            <div>
                <span class="badge badge-success">Kepala Desa</span>
                <h2>Selamat Datang, Kepala Desa</h2>
            </div>
            <a href="{{ route('kepala-desa.hasil-rekomendasi.index') }}" class="btn btn-primary btn-auto">Hasil Rekomendasi</a>
        </section>

        <section class="stat-grid">
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
                    <div><div class="stat-label">Prioritas Utama Terbaru</div><div class="stat-value stat-value-code">{{ $prioritasUtamaTerbaru?->dusun?->nama_dusun ?? '-' }}</div></div>
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
