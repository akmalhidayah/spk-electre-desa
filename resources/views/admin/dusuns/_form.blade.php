<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="form-group">
            <label for="kode_alternatif" class="form-label">Kode Alternatif</label>
            <input
                id="kode_alternatif"
                type="text"
                name="kode_alternatif"
                value="{{ old('kode_alternatif', $dusun->kode_alternatif) }}"
                class="form-control @error('kode_alternatif') is-invalid @enderror"
                maxlength="20"
                placeholder="Contoh: A1"
            >
            @error('kode_alternatif')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="nama_dusun" class="form-label">Nama Dusun <span class="required">*</span></label>
            <input
                id="nama_dusun"
                type="text"
                name="nama_dusun"
                value="{{ old('nama_dusun', $dusun->nama_dusun) }}"
                class="form-control @error('nama_dusun') is-invalid @enderror"
                maxlength="150"
                required
                placeholder="Contoh: Dusun Katute"
            >
            @error('nama_dusun')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="luas_tanah" class="form-label">Luas Tanah</label>
            <input
                id="luas_tanah"
                type="number"
                step="0.01"
                min="0"
                name="luas_tanah"
                value="{{ old('luas_tanah', $dusun->luas_tanah) }}"
                class="form-control @error('luas_tanah') is-invalid @enderror"
                placeholder="Contoh: 125.50"
            >
            @error('luas_tanah')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="jumlah_penduduk" class="form-label">Jumlah Penduduk</label>
            <input
                id="jumlah_penduduk"
                type="number"
                min="0"
                name="jumlah_penduduk"
                value="{{ old('jumlah_penduduk', $dusun->jumlah_penduduk) }}"
                class="form-control @error('jumlah_penduduk') is-invalid @enderror"
                placeholder="Contoh: 850"
            >
            @error('jumlah_penduduk')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status <span class="required">*</span></label>
            <select
                id="status"
                name="status"
                class="form-control @error('status') is-invalid @enderror"
                required
            >
                <option value="aktif" @selected(old('status', $dusun->status ?? 'aktif') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $dusun->status ?? 'aktif') === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group form-group-full">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea
                id="keterangan"
                name="keterangan"
                rows="4"
                class="form-control @error('keterangan') is-invalid @enderror"
                placeholder="Catatan tambahan mengenai dusun"
            >{{ old('keterangan', $dusun->keterangan) }}</textarea>
            @error('keterangan')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.dusuns.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary btn-auto">
            {{ $submitLabel }}
        </button>
    </div>
</form>
