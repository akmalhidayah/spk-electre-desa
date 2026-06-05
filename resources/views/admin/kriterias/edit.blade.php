@extends('layouts.app')

@section('title', 'Edit Kriteria - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Data Kriteria')
@section('page-title', 'Edit Kriteria')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>{{ $kriteria->kode }} - {{ $kriteria->nama_kriteria }}</h2>
                <p>Perbarui kriteria tanpa menghapus histori penilaian alternatif.</p>
            </div>
            <a href="{{ route('admin.kriterias.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.kriterias._form', [
                'action' => route('admin.kriterias.update', $kriteria),
                'method' => 'PUT',
                'submitLabel' => 'Update Kriteria',
            ])
        </section>
    </div>
@endsection
