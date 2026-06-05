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

        <section class="panel" id="ubah-status">
            <h2 class="panel-title">Ubah Status Usulan</h2>
            <p class="panel-text">Perbarui status dan catatan admin untuk memberi kejelasan proses kepada kepala dusun.</p>
            <form method="POST" action="{{ route('admin.usulan.update-status', $usulan) }}" class="form-stack js-confirm status-form" data-title="Ubah Status Usulan?" data-text="Status usulan akan diperbarui. Lanjutkan?" data-icon="question" data-confirm-button="Ya, Ubah">
                @csrf
                @method('PATCH')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status_update" class="form-label">Status</label>
                        <select id="status_update" name="status" class="form-control @error('status') is-invalid @enderror" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $usulan->status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group form-group-full">
                        <label for="catatan_admin_update" class="form-label">Catatan Admin</label>
                        <textarea id="catatan_admin_update" name="catatan_admin" rows="4" class="form-control @error('catatan_admin') is-invalid @enderror" placeholder="Catatan hasil tinjauan admin">{{ old('catatan_admin', $usulan->catatan_admin) }}</textarea>
                        @error('catatan_admin')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-auto">Simpan Status</button>
                </div>
            </form>
        </section>
    </div>
@endsection
