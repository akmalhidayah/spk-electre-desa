@csrf

<div class="form-grid">
    <div class="form-group">
        <label for="tahun" class="form-label">Tahun</label>
        <input id="tahun" type="number" name="tahun" min="2020" max="2100" value="{{ old('tahun', $periode->tahun) }}" class="form-control" required>
        @error('tahun') <small class="form-error">{{ $message }}</small> @enderror
    </div>
    <div class="form-group">
        <label for="nama_periode" class="form-label">Nama Periode</label>
        <input id="nama_periode" type="text" name="nama_periode" value="{{ old('nama_periode', $periode->nama_periode) }}" class="form-control">
        @error('nama_periode') <small class="form-error">{{ $message }}</small> @enderror
    </div>
    <div class="form-group full">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" class="form-control">{{ old('deskripsi', $periode->deskripsi) }}</textarea>
        @error('deskripsi') <small class="form-error">{{ $message }}</small> @enderror
    </div>
    <label class="checkbox-row">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $periode->is_active))>
        <span>Jadikan tahun aktif</span>
    </label>
    <label class="checkbox-row">
        <input type="checkbox" name="is_locked" value="1" @checked(old('is_locked', $periode->is_locked))>
        <span>Kunci periode</span>
    </label>
</div>

<div class="form-actions">
    <a href="{{ route('admin.tahun-perencanaan.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
