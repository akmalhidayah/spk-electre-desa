@extends('layouts.app')

@section('title', 'Welcome Desa - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Welcome Desa')

@section('content')
    <div class="stack welcome-admin-page">
        <section class="page-header-card">
            <div>
                <h2>Pengaturan Landing Page Desa</h2>
                <p>Kelola konten halaman depan desa, profil singkat, peta, dan struktur organisasi yang tampil sebelum login.</p>
            </div>
            <a href="{{ route('landing.index') }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h10v10" /><path d="M7 17 17 7" /></svg>
                Lihat Landing Page
            </a>
        </section>

        <section class="panel">
            @include('admin.welcome-desa.partials.form-profile')
        </section>

        <section class="panel">
            @include('admin.welcome-desa.partials.form-struktur')
        </section>

        <section class="panel">
            @include('admin.welcome-desa.partials.table-struktur')
        </section>
    </div>
@endsection
