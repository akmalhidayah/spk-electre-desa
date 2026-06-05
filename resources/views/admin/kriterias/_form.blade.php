<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="form-group">
            <label for="kode" class="form-label">Kode Kriteria <span class="required">*</span></label>
            <input
                id="kode"
                type="text"
                name="kode"
                value="{{ old('kode', $kriteria->kode) }}"
                class="form-control @error('kode') is-invalid @enderror"
                maxlength="20"
                required
                placeholder="C1"
            >
            @error('kode')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="nama_kriteria" class="form-label">Nama Kriteria <span class="required">*</span></label>
            <input
                id="nama_kriteria"
                type="text"
                name="nama_kriteria"
                value="{{ old('nama_kriteria', $kriteria->nama_kriteria) }}"
                class="form-control @error('nama_kriteria') is-invalid @enderror"
                maxlength="150"
                required
                placeholder="Luas Tanah"
            >
            @error('nama_kriteria')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="bobot" class="form-label">Bobot (%) <span class="required">*</span></label>
            <input
                id="bobot"
                type="number"
                step="0.01"
                min="0"
                max="100"
                name="bobot"
                value="{{ old('bobot', $kriteria->bobot) }}"
                class="form-control @error('bobot') is-invalid @enderror"
                required
                placeholder="20.00"
            >
            @error('bobot')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="tipe" class="form-label">Tipe <span class="required">*</span></label>
            <select
                id="tipe"
                name="tipe"
                class="form-control @error('tipe') is-invalid @enderror"
                required
            >
                <option value="benefit" @selected(old('tipe', $kriteria->tipe ?? 'benefit') === 'benefit')>Benefit</option>
                <option value="cost" @selected(old('tipe', $kriteria->tipe ?? 'benefit') === 'cost')>Cost</option>
            </select>
            @error('tipe')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="urutan" class="form-label">Urutan</label>
            <input
                id="urutan"
                type="number"
                min="0"
                name="urutan"
                value="{{ old('urutan', $kriteria->urutan) }}"
                class="form-control @error('urutan') is-invalid @enderror"
                placeholder="1"
            >
            @error('urutan')
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
                <option value="aktif" @selected(old('status', $kriteria->status ?? 'aktif') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $kriteria->status ?? 'aktif') === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group form-group-full">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="4"
                class="form-control @error('deskripsi') is-invalid @enderror"
                placeholder="Catatan penggunaan kriteria dalam perhitungan ELECTRE"
            >{{ old('deskripsi', $kriteria->deskripsi) }}</textarea>
            @error('deskripsi')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="alert alert-warning">
        Total bobot kriteria aktif harus berjumlah 100% agar perhitungan ELECTRE valid.
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.kriterias.index') }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto">
            {{ $submitLabel }}
        </button>
    </div>
</form>
