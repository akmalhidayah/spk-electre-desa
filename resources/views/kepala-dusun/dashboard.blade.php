@extends('layouts.app')

@section('title', 'Dashboard Kepala Dusun - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Dusun')
@section('page-title', 'Dashboard Kepala Dusun')

@section('content')
    <div class="stack">
        @if (! $dusun)
            <div class="alert alert-warning">
                Akun kepala dusun ini belum terhubung dengan data dusun.
            </div>
        @endif

        <section class="panel">
            <div class="stat-label">Dusun</div>
            <div class="stat-value">{{ $dusun?->nama_dusun ?? '-' }}</div>
        </section>

        <section class="stat-grid">
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
                        <div class="stat-label">Usulan Diajukan</div>
                        <div class="stat-value">{{ number_format($totalDiajukan) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Usulan Diproses</div>
                        <div class="stat-value">{{ number_format($totalDiproses) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v3M12 18v3M4.2 6.2l2.1 2.1M17.7 15.7l2.1 2.1M3 12h3M18 12h3M4.2 17.8l2.1-2.1M17.7 8.3l2.1-2.1" /></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-row">
                    <div>
                        <div class="stat-label">Masuk Prioritas</div>
                        <div class="stat-value">{{ number_format($totalMasukPrioritas) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
