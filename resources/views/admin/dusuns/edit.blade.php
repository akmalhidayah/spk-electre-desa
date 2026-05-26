@extends('layouts.app')

@section('title', 'Edit Dusun - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Data Dusun')
@section('page-title', 'Edit Dusun')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>{{ $dusun->nama_dusun }}</h2>
                <p>Perbarui informasi dusun tanpa menghapus histori penilaian atau perhitungan.</p>
            </div>
            <a href="{{ route('admin.dusuns.index') }}" class="btn btn-light">
                Kembali
            </a>
        </section>

        <section class="panel">
            @include('admin.dusuns._form', [
                'action' => route('admin.dusuns.update', $dusun),
                'method' => 'PUT',
                'submitLabel' => 'Update Dusun',
            ])
        </section>
    </div>
@endsection
