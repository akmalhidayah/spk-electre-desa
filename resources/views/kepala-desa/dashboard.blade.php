@extends('layouts.app')

@section('title', 'Dashboard Kepala Desa - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Desa')
@section('page-title', 'Dashboard Kepala Desa')

@section('content')
    <div class="stack">
        <section class="stat-grid">
            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Perhitungan Selesai</div>
                        <div class="stat-value">{{ number_format($totalSelesai) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Perhitungan Terakhir</div>
                        <div class="stat-value">
                            {{ $perhitunganTerakhir?->kode_perhitungan ?? '-' }}
                        </div>
                        <div class="stat-note">
                            {{ $perhitunganTerakhir?->tahun ?? 'Belum ada data' }}
                        </div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /><path d="M12 7v5l3 2" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Dusun Aktif</div>
                        <div class="stat-value">{{ number_format($totalDusunAktif) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2 class="panel-title">Rekomendasi dan Keputusan</h2>
            <p class="panel-text">
                Kepala desa dapat melihat hasil rekomendasi prioritas pembangunan, meninjau laporan perhitungan, dan menetapkan keputusan akhir berdasarkan hasil ELECTRE.
            </p>
        </section>
    </div>
@endsection
