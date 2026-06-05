@extends('layouts.app')

@section('title', 'Tambah User - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Manajemen User')
@section('page-title', 'Tambah User')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Tambah User</h2>
                <p>Buat akun baru untuk admin, kepala desa, atau kepala dusun.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.users._form', [
                'action' => route('admin.users.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan User',
                'userData' => $userData,
                'roles' => $roles,
                'dusuns' => $dusuns,
                'isEdit' => false,
                'isSelf' => false,
            ])
        </section>
    </div>
@endsection
