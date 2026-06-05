<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="form-group">
            <label for="dusun_id" class="form-label">Dusun <span class="required">*</span></label>
            <select id="dusun_id" name="dusun_id" class="form-control @error('dusun_id') is-invalid @enderror" required>
                <option value="">Pilih dusun</option>
                @foreach ($dusuns as $dusun)
                    <option value="{{ $dusun->id }}" @selected(old('dusun_id', $usulan->dusun_id) == $dusun->id)>{{ $dusun->nama_dusun }}</option>
                @endforeach
            </select>
            @error('dusun_id')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="tahun" class="form-label">Tahun <span class="required">*</span></label>
            <input id="tahun" type="number" min="2020" max="2100" name="tahun" value="{{ old('tahun', $usulan->tahun) }}" class="form-control @error('tahun') is-invalid @enderror" required>
            @error('tahun')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
            <input id="nama_kegiatan" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $usulan->nama_kegiatan) }}" class="form-control @error('nama_kegiatan') is-invalid @enderror" maxlength="200" required placeholder="Contoh: Pembangunan jalan tani">
            @error('nama_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="jumlah_usulan" class="form-label">Jumlah Usulan</label>
            <input id="jumlah_usulan" type="number" min="0" name="jumlah_usulan" value="{{ old('jumlah_usulan', $usulan->jumlah_usulan) }}" class="form-control @error('jumlah_usulan') is-invalid @enderror" placeholder="Contoh: 1">
            @error('jumlah_usulan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="estimasi_anggaran" class="form-label">Estimasi Anggaran</label>
            <input id="estimasi_anggaran" type="number" min="0" step="0.01" name="estimasi_anggaran" value="{{ old('estimasi_anggaran', $usulan->estimasi_anggaran) }}" class="form-control @error('estimasi_anggaran') is-invalid @enderror" placeholder="Contoh: 1000000">
            @error('estimasi_anggaran')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $usulan->status ?? 'diajukan') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            @error('status')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Jelaskan kebutuhan, lokasi, dan manfaat kegiatan">{{ old('deskripsi', $usulan->deskripsi) }}</textarea>
            @error('deskripsi')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="catatan_admin" class="form-label">Catatan Admin</label>
            <textarea id="catatan_admin" name="catatan_admin" rows="3" class="form-control @error('catatan_admin') is-invalid @enderror" placeholder="Catatan tinjauan admin">{{ old('catatan_admin', $usulan->catatan_admin) }}</textarea>
            @error('catatan_admin')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.usulan.index') }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto">{{ $submitLabel }}</button>
    </div>
</form>
