<form method="POST" action="{{ route('admin.welcome-desa.struktur.store') }}" enctype="multipart/form-data" class="form-stack">
    @csrf

    <div>
        <h3 class="panel-title">Tambah Struktur Organisasi</h3>
        <p class="panel-text">Data aktif akan tampil sebagai card pada section Struktur Organisasi di landing page.</p>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label for="struktur_nama" class="form-label">Nama <span class="required">*</span></label>
            <input id="struktur_nama" type="text" name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" maxlength="150" required>
            @error('nama')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="struktur_jabatan" class="form-label">Jabatan <span class="required">*</span></label>
            <input id="struktur_jabatan" type="text" name="jabatan" value="{{ old('jabatan') }}" class="form-control @error('jabatan') is-invalid @enderror" maxlength="150" required>
            @error('jabatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="struktur_urutan" class="form-label">Urutan</label>
            <input id="struktur_urutan" type="number" name="urutan" value="{{ old('urutan', 0) }}" class="form-control @error('urutan') is-invalid @enderror">
            @error('urutan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="struktur_foto" class="form-label">Foto</label>
            <input id="struktur_foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control @error('foto') is-invalid @enderror">
            @error('foto')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="struktur_deskripsi" class="form-label">Keterangan</label>
            <textarea id="struktur_deskripsi" name="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label class="checkbox-row">
                <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', true))>
                <span>Aktif</span>
            </label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-auto">Tambah Struktur</button>
    </div>
</form>
