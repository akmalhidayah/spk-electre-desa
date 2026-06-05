@extends('layouts.app')

@section('title', 'Dashboard Kepala Dusun - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Dusun')
@section('page-title', 'Dashboard Kepala Dusun')

@section('content')
    <div class="stack">
        <section class="dashboard-hero">
            <div>
                <span class="badge badge-warning">Kepala Dusun</span>
                <h2>Selamat Datang, Kepala Dusun</h2>
                <p>{{ $dusun ? 'Dusun '.$dusun->nama_dusun : 'Akun Anda belum terhubung dengan data dusun.' }}</p>
            </div>
            <a href="{{ route('kepala-dusun.usulan.create') }}" class="btn btn-primary btn-auto">Ajukan Usulan</a>
        </section>

        @if (! $dusun)
            <div class="alert alert-warning">
                Akun Anda belum terhubung dengan data dusun. Silakan hubungi admin agar dapat mengajukan usulan pembangunan.
            </div>
        @endif

        <section class="stat-grid">
            <article class="stat-card">
                <div class="stat-card-row">
                    <div><div class="stat-label">Total Usulan</div><div class="stat-value">{{ number_format($totalUsulan) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg></span>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-card-row">
                    <div><div class="stat-label">Diajukan</div><div class="stat-value">{{ number_format($totalDiajukan) }}</div></div>
                    <span class="stat-icon icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4l3 2" /><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-card-row">
                    <div><div class="stat-label">Diproses</div><div class="stat-value">{{ number_format($totalDiproses) }}</div></div>
                    <span class="stat-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 0 1 14-5" /><path d="M18 3v4h-4" /><path d="M20 12a8 8 0 0 1-14 5" /><path d="M6 21v-4h4" /></svg></span>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-card-row">
                    <div><div class="stat-label">Diterima</div><div class="stat-value">{{ number_format($totalDiterima) }}</div></div>
                    <span class="stat-icon icon-emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20 6-11 11-5-5" /></svg></span>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-card-row">
                    <div><div class="stat-label">Masuk Prioritas</div><div class="stat-value">{{ number_format($totalMasukPrioritas) }}</div></div>
                    <span class="stat-icon icon-violet"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1-4.4-4.3 6.1-.9Z" /></svg></span>
                </div>
            </article>
        </section>

        <section class="quick-actions">
            <a href="{{ route('kepala-dusun.usulan.create') }}" class="quick-action-card">Ajukan Usulan</a>
            <a href="{{ route('kepala-dusun.usulan.index') }}" class="quick-action-card">Riwayat Usulan</a>
        </section>

        <section class="panel">
            <h2 class="panel-title">Usulan Terbaru</h2>
            @if ($latestUsulans->count() > 0)
                <div class="mini-list">
                    @foreach ($latestUsulans as $usulan)
                        <div class="mini-list-item">
                            <div>
                                <strong>{{ $usulan->nama_kegiatan }}</strong>
                                <span>{{ $usulan->tahun }} - {{ $usulan->created_at?->format('d/m/Y') }}</span>
                            </div>
                            <span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state compact-empty">
                    <h3>Belum ada usulan</h3>
                    <p>Ajukan kebutuhan pembangunan dusun Anda agar dapat ditinjau admin.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
