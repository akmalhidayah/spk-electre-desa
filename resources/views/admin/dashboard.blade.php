@extends('layouts.app')

@section('title', 'Dashboard Admin - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
    <div class="stack">
        <section class="stat-grid">
            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Dusun</div>
                        <div class="stat-value">{{ number_format($totalDusun) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Kriteria</div>
                        <div class="stat-value">{{ number_format($totalKriteria) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Usulan</div>
                        <div class="stat-value">{{ number_format($totalUsulan) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Total Perhitungan ELECTRE</div>
                        <div class="stat-value">{{ number_format($totalPerhitungan) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01" /></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2 class="panel-title">Pengelolaan Data SPK</h2>
            <p class="panel-text">
                Admin mengelola data dusun, kriteria, usulan pembangunan, penilaian alternatif, proses perhitungan ELECTRE, hasil rekomendasi, dan laporan prioritas pembangunan antar dusun.
            </p>
        </section>
    </div>
@endsection
