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
            <label for="jumlah_usulan" class="form-label">Jumlah Usulan</label>
            <input id="jumlah_usulan" type="number" min="0" name="jumlah_usulan" value="{{ old('jumlah_usulan', $usulan->jumlah_usulan) }}" class="form-control @error('jumlah_usulan') is-invalid @enderror" placeholder="Contoh: 1">
            @error('jumlah_usulan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
            <input id="nama_kegiatan" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $usulan->nama_kegiatan) }}" class="form-control @error('nama_kegiatan') is-invalid @enderror" maxlength="200" required placeholder="Contoh: Perbaikan drainase dusun">
            @error('nama_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="estimasi_anggaran" class="form-label">Estimasi Anggaran</label>
            <input id="estimasi_anggaran" type="number" min="0" step="0.01" name="estimasi_anggaran" value="{{ old('estimasi_anggaran', $usulan->estimasi_anggaran) }}" class="form-control @error('estimasi_anggaran') is-invalid @enderror" placeholder="Contoh: 1000000">
            @error('estimasi_anggaran')<div class="field-error">{{ $message }}</div>@enderror
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
