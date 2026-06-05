@extends('layouts.app')

@section('title', 'Tambah Usulan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Usulan Pembangunan')
@section('page-title', 'Tambah Usulan')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Tambah Usulan Pembangunan</h2>
                <p>Input usulan untuk dusun sebagai bahan pendukung penilaian ELECTRE.</p>
            </div>
            <a href="{{ route('admin.usulan.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.usulan._form', [
                'action' => route('admin.usulan.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Usulan',
            ])
        </section>
    </div>
@endsection
