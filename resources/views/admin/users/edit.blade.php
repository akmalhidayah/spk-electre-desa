@extends('layouts.app')

@section('title', 'Edit User - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Manajemen User')
@section('page-title', 'Edit User')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Edit User</h2>
                <p>Perbarui data akun pengguna dan hak akses sistem.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            @include('admin.users._form', [
                'action' => route('admin.users.update', $userData),
                'method' => 'PUT',
                'submitLabel' => 'Update User',
                'userData' => $userData,
                'roles' => $roles,
                'dusuns' => $dusuns,
                'isEdit' => true,
                'isSelf' => $isSelf,
            ])
        </section>
    </div>
@endsection
