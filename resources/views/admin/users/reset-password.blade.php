@extends('layouts.app')

@section('title', 'Reset Password User - SPK ELECTRE Desa')
@section('eyebrow', 'Admin / Manajemen User')
@section('page-title', 'Reset Password')

@section('content')
    <div class="stack max-width-form">
        <section class="page-header-card">
            <div>
                <h2>Reset Password</h2>
                <p>Atur ulang password pengguna tanpa mengubah data akun lainnya.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Kembali</a>
        </section>

        <section class="panel">
            <div class="meta-grid">
                <div><dt>Nama</dt><dd>{{ $userData->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $userData->email }}</dd></div>
                <div><dt>Role</dt><dd>{{ $userData->role_label }}</dd></div>
                <div><dt>Dusun</dt><dd>{{ $userData->dusun?->nama_dusun ?? '-' }}</dd></div>
            </div>
        </section>

        <section class="panel">
            <form
                method="POST"
                action="{{ route('admin.users.reset-password.update', $userData) }}"
                class="form-stack js-confirm"
                data-title="Reset Password?"
                data-text="Password user akan diganti dengan password baru."
                data-icon="question"
                data-confirm-button="Ya, Reset"
            >
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div class="form-group">
                        <label for="password" class="form-label">Password Baru <span class="required">*</span></label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="required">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="Ulangi password baru">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Kembali</a>
                    <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Mereset...">Simpan Password Baru</button>
                </div>
            </form>
        </section>
    </div>
@endsection
