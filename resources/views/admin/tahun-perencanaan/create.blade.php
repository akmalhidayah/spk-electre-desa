@extends('layouts.app')

@section('title', 'Tambah Tahun Perencanaan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Tambah Tahun Perencanaan')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('admin.tahun-perencanaan.store') }}" class="stack">
            @include('admin.tahun-perencanaan._form')
        </form>
    </section>
@endsection
