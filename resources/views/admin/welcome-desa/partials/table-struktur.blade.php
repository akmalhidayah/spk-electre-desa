<div class="matrix-toolbar">
    <div>
        <h3 class="panel-title">Data Struktur Organisasi</h3>
        <p class="panel-text">Nonaktifkan data yang tidak ingin ditampilkan di landing page.</p>
    </div>
</div>

@if ($strukturList->count() > 0)
    <div class="table-wrap desktop-table">
        <table class="data-table welcome-structure-table">
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($strukturList as $item)
                    <tr>
                        <td>{{ $item->urutan }}</td>
                        <td>
                            @if ($item->fotoUrl())
                                <img src="{{ $item->fotoUrl() }}" alt="Foto {{ $item->nama }}" class="welcome-avatar-preview">
                            @else
                                <span class="welcome-avatar-placeholder">{{ strtoupper(substr($item->nama, 0, 1)) }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->nama }}</strong>
                            @if ($item->deskripsi)
                                <small>{{ \Illuminate\Support\Str::limit($item->deskripsi, 80) }}</small>
                            @endif
                        </td>
                        <td>{{ $item->jabatan }}</td>
                        <td>
                            <span class="badge {{ $item->status_aktif ? 'badge-success' : 'badge-muted' }}">
                                {{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <details class="welcome-edit-details">
                                    <summary class="btn btn-sm btn-light">Edit</summary>
                                    <div class="welcome-edit-panel">
                                        <form method="POST" action="{{ route('admin.welcome-desa.struktur.update', $item) }}" enctype="multipart/form-data" class="form-stack">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label class="form-label" for="nama_{{ $item->id }}">Nama</label>
                                                    <input id="nama_{{ $item->id }}" type="text" name="nama" value="{{ old('nama', $item->nama) }}" class="form-control" maxlength="150" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="jabatan_{{ $item->id }}">Jabatan</label>
                                                    <input id="jabatan_{{ $item->id }}" type="text" name="jabatan" value="{{ old('jabatan', $item->jabatan) }}" class="form-control" maxlength="150" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="urutan_{{ $item->id }}">Urutan</label>
                                                    <input id="urutan_{{ $item->id }}" type="number" name="urutan" value="{{ old('urutan', $item->urutan) }}" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="foto_{{ $item->id }}">Ganti Foto</label>
                                                    <input id="foto_{{ $item->id }}" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control">
                                                </div>
                                                <div class="form-group form-group-full">
                                                    <label class="form-label" for="deskripsi_{{ $item->id }}">Keterangan</label>
                                                    <textarea id="deskripsi_{{ $item->id }}" name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                                                </div>
                                                <div class="form-group form-group-full">
                                                    <label class="checkbox-row">
                                                        <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $item->status_aktif))>
                                                        <span>Aktif</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-auto">Simpan Perubahan</button>
                                        </form>
                                    </div>
                                </details>

                                <form method="POST" action="{{ route('admin.welcome-desa.struktur.toggle', $item) }}" class="js-confirm" data-title="Ubah status struktur?" data-text="Status tampil struktur organisasi akan diubah." data-icon="question" data-confirm-button="Ya, ubah">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-secondary">{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>

                                <form method="POST" action="{{ route('admin.welcome-desa.struktur.destroy', $item) }}" class="js-confirm" data-title="Hapus struktur organisasi?" data-text="Data struktur organisasi ini akan dihapus permanen." data-icon="warning" data-confirm-button="Ya, hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mobile-list">
        @foreach ($strukturList as $item)
            <article class="mobile-card">
                <div class="mobile-card-head">
                    <div>
                        <h3>{{ $item->nama }}</h3>
                        <p>{{ $item->jabatan }}</p>
                    </div>
                    <span class="badge {{ $item->status_aktif ? 'badge-success' : 'badge-muted' }}">{{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <dl class="meta-grid">
                    <div><dt>Urutan</dt><dd>{{ $item->urutan }}</dd></div>
                    <div><dt>Keterangan</dt><dd>{{ $item->deskripsi ?: '-' }}</dd></div>
                </dl>
                <details class="mobile-edit-details">
                    <summary class="btn btn-sm btn-light">Edit Struktur</summary>
                    <form method="POST" action="{{ route('admin.welcome-desa.struktur.update', $item) }}" enctype="multipart/form-data" class="form-stack mobile-edit-form">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="mobile_nama_{{ $item->id }}">Nama</label>
                                <input id="mobile_nama_{{ $item->id }}" type="text" name="nama" value="{{ old('nama', $item->nama) }}" class="form-control" maxlength="150" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="mobile_jabatan_{{ $item->id }}">Jabatan</label>
                                <input id="mobile_jabatan_{{ $item->id }}" type="text" name="jabatan" value="{{ old('jabatan', $item->jabatan) }}" class="form-control" maxlength="150" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="mobile_urutan_{{ $item->id }}">Urutan</label>
                                <input id="mobile_urutan_{{ $item->id }}" type="number" name="urutan" value="{{ old('urutan', $item->urutan) }}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="mobile_foto_{{ $item->id }}">Ganti Foto</label>
                                <input id="mobile_foto_{{ $item->id }}" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control">
                            </div>
                            <div class="form-group form-group-full">
                                <label class="form-label" for="mobile_deskripsi_{{ $item->id }}">Keterangan</label>
                                <textarea id="mobile_deskripsi_{{ $item->id }}" name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                            </div>
                            <div class="form-group form-group-full">
                                <label class="checkbox-row">
                                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $item->status_aktif))>
                                    <span>Aktif</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </details>
                <div class="mobile-actions">
                    <form method="POST" action="{{ route('admin.welcome-desa.struktur.toggle', $item) }}" class="js-confirm" data-title="Ubah status struktur?" data-text="Status tampil struktur organisasi akan diubah." data-icon="question" data-confirm-button="Ya, ubah">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-secondary">{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.welcome-desa.struktur.destroy', $item) }}" class="js-confirm" data-title="Hapus struktur organisasi?" data-text="Data struktur organisasi ini akan dihapus permanen." data-icon="warning" data-confirm-button="Ya, hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
        </div>
        <h3>Struktur organisasi belum tersedia</h3>
        <p>Tambahkan perangkat desa melalui form di atas agar tampil pada landing page.</p>
    </div>
@endif
