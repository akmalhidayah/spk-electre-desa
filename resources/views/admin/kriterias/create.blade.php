@extends('layouts.app')

@section('title', 'Tambah Kriteria - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Data Kriteria')
@section('page-title', 'Tambah Kriteria')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Tambah Kriteria</h2>
                <p>Total bobot kriteria aktif harus berjumlah 100% agar perhitungan ELECTRE valid.</p>
            </div>
            <a href="{{ route('admin.kriterias.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.kriterias._form', [
                'action' => route('admin.kriterias.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Kriteria',
            ])
        </section>
    </div>
@endsection
