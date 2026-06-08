@extends('layouts.app')

@section('title', 'Profil Saya - SPK ELECTRE Desa')
@section('eyebrow', 'Akun')
@section('page-title', 'Profil Saya')

@section('content')
    <div class="stack max-width-form profile-page">
        <section class="page-header-card">
            <div>
                <h2>Profil Saya</h2>
                <p>Kelola identitas akun dan password yang digunakan untuk masuk ke sistem.</p>
            </div>
        </section>

        <section class="panel profile-panel">
            <div class="profile-grid">
                <div>
                    <h2 class="panel-title">Data Profil</h2>
                    <p class="panel-text">Perbarui nama dan email akun Anda.</p>

                    <form method="POST" action="{{ route('profile.update') }}" class="form-stack profile-form">
                        @csrf
                        @method('PATCH')

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label">Nama <span class="required">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name', $userData->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="150">
                                @error('name')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="required">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email', $userData->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="150">
                                @error('email')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ url()->previous() }}" class="btn btn-light">Kembali</a>
                            <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Menyimpan...">Simpan Profil</button>
                        </div>
                    </form>
                </div>

                <div id="update-password">
                    <h2 class="panel-title">Update Password</h2>
                    <p class="panel-text">Gunakan password yang kuat dan mudah Anda ingat.</p>

                    <form method="POST" action="{{ route('profile.password.update') }}" class="form-stack profile-form">
                        @csrf
                        @method('PATCH')

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="current_password" class="form-label">Password Saat Ini <span class="required">*</span></label>
                                <input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">Password Baru <span class="required">*</span></label>
                                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                                @error('password')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group form-group-full">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="required">*</span></label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Mengubah...">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
