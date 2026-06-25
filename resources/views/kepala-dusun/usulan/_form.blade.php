<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="form-group form-group-full">
            <label class="form-label">Dusun</label>
            <input type="text" value="{{ $dusun?->nama_dusun ?? 'Belum terhubung dengan dusun' }}" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label for="tahun" class="form-label">Tahun <span class="required">*</span></label>
            <input id="tahun" type="number" min="2020" max="2100" name="tahun" value="{{ old('tahun', $usulan->tahun) }}" class="form-control @error('tahun') is-invalid @enderror" required>
            @error('tahun')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Status Awal</label>
            <input type="text" value="Diajukan" class="form-control" readonly>
        </div>

        <div class="form-group form-group-full">
            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
            <input id="nama_kegiatan" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $usulan->nama_kegiatan) }}" class="form-control @error('nama_kegiatan') is-invalid @enderror" maxlength="255" required placeholder="Contoh: Pengecoran Lanjutan Jln. Poros Pasar">
            @error('nama_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="lokasi_kegiatan" class="form-label">Lokasi Kegiatan</label>
            <input id="lokasi_kegiatan" type="text" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan', $usulan->lokasi_kegiatan) }}" class="form-control @error('lokasi_kegiatan') is-invalid @enderror" maxlength="255" placeholder="Contoh: RT.001/RW.001 Dusun Katute">
            @error('lokasi_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="prakiraan_volume" class="form-label">Prakiraan Volume</label>
            <input id="prakiraan_volume" type="number" min="0" step="0.01" name="prakiraan_volume" value="{{ old('prakiraan_volume', $usulan->prakiraan_volume) }}" class="form-control @error('prakiraan_volume') is-invalid @enderror" placeholder="Contoh: 120">
            @error('prakiraan_volume')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="satuan" class="form-label">Satuan</label>
            <input id="satuan" type="text" name="satuan" value="{{ old('satuan', $usulan->satuan) }}" class="form-control @error('satuan') is-invalid @enderror" maxlength="50" placeholder="Contoh: Meter, Unit, Orang">
            @error('satuan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_lk" class="form-label">Penerima Manfaat LK</label>
            <input id="penerima_manfaat_lk" type="number" min="0" name="penerima_manfaat_lk" value="{{ old('penerima_manfaat_lk', $usulan->penerima_manfaat_lk) }}" class="form-control @error('penerima_manfaat_lk') is-invalid @enderror" placeholder="Contoh: 500">
            @error('penerima_manfaat_lk')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_pr" class="form-label">Penerima Manfaat PR</label>
            <input id="penerima_manfaat_pr" type="number" min="0" name="penerima_manfaat_pr" value="{{ old('penerima_manfaat_pr', $usulan->penerima_manfaat_pr) }}" class="form-control @error('penerima_manfaat_pr') is-invalid @enderror" placeholder="Contoh: 400">
            @error('penerima_manfaat_pr')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_a_rtm" class="form-label">Penerima Manfaat A-RTM</label>
            <input id="penerima_manfaat_a_rtm" type="number" min="0" name="penerima_manfaat_a_rtm" value="{{ old('penerima_manfaat_a_rtm', $usulan->penerima_manfaat_a_rtm) }}" class="form-control @error('penerima_manfaat_a_rtm') is-invalid @enderror" placeholder="Contoh: 0">
            @error('penerima_manfaat_a_rtm')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="kategori_kegiatan" class="form-label">Kategori Kegiatan</label>
            <input id="kategori_kegiatan" type="text" name="kategori_kegiatan" value="{{ old('kategori_kegiatan', $usulan->kategori_kegiatan) }}" class="form-control @error('kategori_kegiatan') is-invalid @enderror" maxlength="100" list="kategori-kegiatan-list" placeholder="Contoh: Infrastruktur Jalan">
            <datalist id="kategori-kegiatan-list">
                <option value="Infrastruktur Jalan">
                <option value="Infrastruktur Jembatan">
                <option value="Infrastruktur/Talud">
                <option value="Air Bersih">
                <option value="Irigasi">
                <option value="Kesehatan">
                <option value="Pendidikan">
                <option value="Pertanian">
                <option value="Peternakan">
                <option value="Pemberdayaan">
                <option value="Pemerintahan">
                <option value="Sosial">
            </datalist>
            @error('kategori_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Jelaskan kebutuhan, lokasi, dan manfaat kegiatan">{{ old('deskripsi', $usulan->deskripsi) }}</textarea>
            @error('deskripsi')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="alert alert-warning">Status usulan otomatis diajukan. Perubahan status hanya dapat dilakukan oleh admin.</div>

    <div class="form-actions">
        <a href="{{ route('kepala-dusun.usulan.index') }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto">{{ $submitLabel }}</button>
    </div>
</form>
