@extends('layouts.app')

@section('title', 'Ajukan Usulan - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Dusun / Usulan')
@section('page-title', 'Ajukan Usulan')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Ajukan Usulan Pembangunan</h2>
                <p>Status usulan baru otomatis menjadi diajukan dan akan ditinjau oleh admin.</p>
            </div>
            <a href="{{ route('kepala-dusun.usulan.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('kepala-dusun.usulan._form', [
                'action' => route('kepala-dusun.usulan.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Usulan',
            ])
        </section>
    </div>
@endsection
