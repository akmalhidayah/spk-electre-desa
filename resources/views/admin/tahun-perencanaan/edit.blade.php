@extends('layouts.app')

@section('title', 'Edit Tahun Perencanaan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Edit Tahun Perencanaan')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('admin.tahun-perencanaan.update', $periode) }}" class="stack">
            @method('PUT')
            @include('admin.tahun-perencanaan._form')
        </form>
    </section>
@endsection
