@extends('layouts.app')

@section('title', 'Edit Usulan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Usulan Pembangunan')
@section('page-title', 'Edit Usulan')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>{{ $usulan->nama_kegiatan }}</h2>
                <p>Pengaju: {{ $usulan->pengaju?->name ?? 'Admin' }}. Dibuat {{ $usulan->created_at?->format('d/m/Y H:i') }}, diperbarui {{ $usulan->updated_at?->format('d/m/Y H:i') }}.</p>
            </div>
            <a href="{{ route('admin.usulan.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.usulan._form', [
                'action' => route('admin.usulan.update', $usulan),
                'method' => 'PUT',
                'submitLabel' => 'Update Usulan',
            ])
        </section>

    </div>
@endsection
