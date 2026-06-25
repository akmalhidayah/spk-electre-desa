@extends('layouts.app')

@section('title', 'Manajemen User - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Manajemen User')

@section('content')
    @php
        $roleBadge = [
            'admin' => 'badge-success',
            'kepala_desa' => 'badge-info',
            'kepala_dusun' => 'badge-warning',
        ];
    @endphp

    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Manajemen User</h2>
                <p>Kelola akun pengguna sistem berdasarkan role dan hak akses.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-auto">
                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                Tambah User
            </a>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar user-filter">
                <div class="filter-field grow">
                    <label for="q" class="form-label">Pencarian</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari nama atau email">
                </div>

                <div class="filter-field">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="dusun_id" class="form-label">Dusun</label>
                    <select id="dusun_id" name="dusun_id" class="form-control">
                        <option value="">Semua Dusun</option>
                        @foreach ($dusuns as $dusun)
                            <option value="{{ $dusun->id }}" @selected((string) $filters['dusun_id'] === (string) $dusun->id)>{{ $dusun->nama_dusun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Cari
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($users->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Dusun</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php($isSelf = auth()->id() === $user->id)
                                <tr>
                                    <td>{{ ($users->firstItem() ?? 0) + $loop->index }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        @if ($isSelf)
                                            <small><span class="badge badge-muted">Akun Anda</span></small>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge {{ $roleBadge[$user->role] ?? 'badge-muted' }}">{{ $user->role_label }}</span></td>
                                    <td>{{ $user->dusun?->nama_dusun ?? '-' }}</td>
                                    <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-muted' }}">{{ $user->status_label }}</span></td>
                                    <td>{{ $user->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <div class="action-group icon-actions">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit user" aria-label="Edit user">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                            </a>
                                            <a href="{{ route('admin.users.reset-password', $user) }}" class="btn btn-sm btn-light action-icon-btn" title="Reset password" aria-label="Reset password">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" /><path d="M5 11h14v10H5Z" /><path d="M12 15v2" /></svg>
                                            </a>
                                            @unless ($isSelf)
                                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="js-confirm" data-title="Ubah Status User?" data-text="Status aktif user akan diperbarui." data-icon="warning" data-confirm-button="Ya, Ubah">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10" /><path d="M18.4 6.6a9 9 0 1 1-12.8 0" /></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="js-confirm" data-title="Hapus User?" data-text="User yang memiliki histori data tidak dapat dihapus. Sebaiknya nonaktifkan user." data-icon="warning" data-confirm-button="Ya, Hapus">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus user" aria-label="Hapus user">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach ($users as $user)
                        @php($isSelf = auth()->id() === $user->id)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <h3>{{ $user->name }}</h3>
                                    <p>{{ $user->email }}</p>
                                </div>
                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-muted' }}">{{ $user->status_label }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Role</dt><dd>{{ $user->role_label }}</dd></div>
                                <div><dt>Dusun</dt><dd>{{ $user->dusun?->nama_dusun ?? '-' }}</dd></div>
                                <div><dt>Terdaftar</dt><dd>{{ $user->created_at?->format('d/m/Y') ?? '-' }}</dd></div>
                                @if ($isSelf)<div><dt>Catatan</dt><dd>Akun Anda</dd></div>@endif
                            </dl>
                            <div class="mobile-actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit user" aria-label="Edit user">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                </a>
                                <a href="{{ route('admin.users.reset-password', $user) }}" class="btn btn-sm btn-light action-icon-btn" title="Reset password" aria-label="Reset password">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" /><path d="M5 11h14v10H5Z" /><path d="M12 15v2" /></svg>
                                </a>
                                @unless ($isSelf)
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="js-confirm" data-title="Ubah Status User?" data-text="Status aktif user akan diperbarui." data-icon="warning" data-confirm-button="Ya, Ubah">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10" /><path d="M18.4 6.6a9 9 0 1 1-12.8 0" /></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="js-confirm" data-title="Hapus User?" data-text="User yang memiliki histori data tidak dapat dihapus. Sebaiknya nonaktifkan user." data-icon="warning" data-confirm-button="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus user" aria-label="Hapus user">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">
                    {{ $users->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                    </div>
                    <h3>Data user belum ditemukan</h3>
                    <p>Tambahkan user baru atau ubah filter pencarian yang sedang digunakan.</p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-auto">Tambah User</a>
                </div>
            @endif
        </section>
    </div>
@endsection
