@extends('layouts.app')

@section('title', 'Edit Usulan - SPK ELECTRE Desa')
@section('eyebrow', 'Kepala Dusun / Usulan')
@section('page-title', 'Edit Usulan')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>{{ $usulan->nama_kegiatan }}</h2>
                <p>Usulan hanya dapat diubah selama status masih diajukan.</p>
            </div>
            <a href="{{ route('kepala-dusun.usulan.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('kepala-dusun.usulan._form', [
                'action' => route('kepala-dusun.usulan.update', $usulan),
                'method' => 'PUT',
                'submitLabel' => 'Update Usulan',
            ])
        </section>
    </div>
@endsection
