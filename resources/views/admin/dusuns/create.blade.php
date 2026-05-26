@extends('layouts.app')

@section('title', 'Tambah Dusun - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Data Dusun')
@section('page-title', 'Tambah Dusun')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Form Tambah Dusun</h2>
                <p>Isi data dusun sebagai alternatif perhitungan prioritas pembangunan.</p>
            </div>
            <a href="{{ route('admin.dusuns.index') }}" class="btn btn-light">
                Kembali
            </a>
        </section>

        <section class="panel">
            @include('admin.dusuns._form', [
                'action' => route('admin.dusuns.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Dusun',
            ])
        </section>
    </div>
@endsection
