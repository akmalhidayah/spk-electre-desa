<form method="POST" action="{{ route('admin.welcome-desa.update') }}" enctype="multipart/form-data" class="form-stack">
    @csrf
    @method('PUT')

    <div>
        <h3 class="panel-title">Profil Landing Page</h3>
        <p class="panel-text">Data ini digunakan pada section Home, Infografis, dan footer landing page.</p>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label for="nama_desa" class="form-label">Nama Desa</label>
            <input id="nama_desa" type="text" name="nama_desa" value="{{ old('nama_desa', $setting->nama_desa) }}" class="form-control @error('nama_desa') is-invalid @enderror" maxlength="150">
            @error('nama_desa')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="kecamatan" class="form-label">Kecamatan</label>
            <input id="kecamatan" type="text" name="kecamatan" value="{{ old('kecamatan', $setting->kecamatan) }}" class="form-control @error('kecamatan') is-invalid @enderror" maxlength="150">
            @error('kecamatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="kabupaten" class="form-label">Kabupaten</label>
            <input id="kabupaten" type="text" name="kabupaten" value="{{ old('kabupaten', $setting->kabupaten) }}" class="form-control @error('kabupaten') is-invalid @enderror" maxlength="150">
            @error('kabupaten')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="provinsi" class="form-label">Provinsi</label>
            <input id="provinsi" type="text" name="provinsi" value="{{ old('provinsi', $setting->provinsi) }}" class="form-control @error('provinsi') is-invalid @enderror" maxlength="150">
            @error('provinsi')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $setting->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="150">
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="telepon" class="form-label">Telepon</label>
            <input id="telepon" type="text" name="telepon" value="{{ old('telepon', $setting->telepon) }}" class="form-control @error('telepon') is-invalid @enderror" maxlength="50">
            @error('telepon')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $setting->alamat) }}</textarea>
            @error('alamat')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="logo_desa" class="form-label">Logo Desa</label>
            @if ($setting->logoUrl())
                <img src="{{ $setting->logoUrl() }}" alt="Logo desa saat ini" class="welcome-image-preview">
            @endif
            <input id="logo_desa" type="file" name="logo_desa" accept="image/jpeg,image/png,image/webp" class="form-control @error('logo_desa') is-invalid @enderror">
            @error('logo_desa')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="hero_image" class="form-label">Hero Image</label>
            @if ($setting->heroImageUrl())
                <img src="{{ $setting->heroImageUrl() }}" alt="Hero image saat ini" class="welcome-image-preview wide">
            @endif
            <input id="hero_image" type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="form-control @error('hero_image') is-invalid @enderror">
            @error('hero_image')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="judul_welcome" class="form-label">Judul Welcome</label>
            <input id="judul_welcome" type="text" name="judul_welcome" value="{{ old('judul_welcome', $setting->judul_welcome) }}" class="form-control @error('judul_welcome') is-invalid @enderror">
            @error('judul_welcome')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="deskripsi_welcome" class="form-label">Deskripsi Welcome</label>
            <textarea id="deskripsi_welcome" name="deskripsi_welcome" rows="4" class="form-control @error('deskripsi_welcome') is-invalid @enderror">{{ old('deskripsi_welcome', $setting->deskripsi_welcome) }}</textarea>
            @error('deskripsi_welcome')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="visi" class="form-label">Visi</label>
            <textarea id="visi" name="visi" rows="4" class="form-control @error('visi') is-invalid @enderror">{{ old('visi', $setting->visi) }}</textarea>
            @error('visi')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="misi" class="form-label">Misi</label>
            <textarea id="misi" name="misi" rows="5" class="form-control @error('misi') is-invalid @enderror" placeholder="Tulis setiap poin misi pada baris baru">{{ old('misi', $setting->misi) }}</textarea>
            @error('misi')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="judul_infografis" class="form-label">Judul Infografis</label>
            <input id="judul_infografis" type="text" name="judul_infografis" value="{{ old('judul_infografis', $setting->judul_infografis) }}" class="form-control @error('judul_infografis') is-invalid @enderror">
            @error('judul_infografis')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="maps_link" class="form-label">Link Google Maps</label>
            <input id="maps_link" type="url" name="maps_link" value="{{ old('maps_link', $setting->maps_link) }}" class="form-control @error('maps_link') is-invalid @enderror" placeholder="https://maps.google.com/...">
            @error('maps_link')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="deskripsi_infografis" class="form-label">Deskripsi Infografis</label>
            <textarea id="deskripsi_infografis" name="deskripsi_infografis" rows="3" class="form-control @error('deskripsi_infografis') is-invalid @enderror">{{ old('deskripsi_infografis', $setting->deskripsi_infografis) }}</textarea>
            @error('deskripsi_infografis')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="maps_embed" class="form-label">Iframe Google Maps Embed</label>
            <textarea id="maps_embed" name="maps_embed" rows="4" class="form-control @error('maps_embed') is-invalid @enderror" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?...&quot;></iframe>">{{ old('maps_embed', $setting->maps_embed) }}</textarea>
            <p class="field-hint">Hanya iframe Google Maps yang akan ditampilkan di landing page.</p>
            @error('maps_embed')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="gambar_peta" class="form-label">Gambar Peta</label>
            @if ($setting->gambarPetaUrl())
                <img src="{{ $setting->gambarPetaUrl() }}" alt="Gambar peta saat ini" class="welcome-image-preview wide">
            @endif
            <input id="gambar_peta" type="file" name="gambar_peta" accept="image/jpeg,image/png,image/webp" class="form-control @error('gambar_peta') is-invalid @enderror">
            @error('gambar_peta')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group welcome-status-field">
            <label class="form-label">Status Landing Page</label>
            <label class="checkbox-row">
                <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $setting->status_aktif))>
                <span>Aktif dan tampil di halaman depan</span>
            </label>
            @error('status_aktif')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-auto">Simpan Profil Landing Page</button>
    </div>
</form>
