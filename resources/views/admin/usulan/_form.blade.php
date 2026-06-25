<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @php
        $selectedTipe = old('tipe_usulan', $usulan->tipe_usulan ?? \App\Models\UsulanPembangunan::TIPE_DUSUN);
        $selectedDusunTerkait = collect(old('dusun_terkait_ids', $usulan->relationLoaded('dusunsTerkait') ? $usulan->dusunsTerkait->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    @endphp

    <div class="form-grid">
        <div class="form-group">
            <label for="tipe_usulan" class="form-label">Tipe Usulan <span class="required">*</span></label>
            <select id="tipe_usulan" name="tipe_usulan" class="form-control @error('tipe_usulan') is-invalid @enderror" required>
                @foreach ($tipeUsulans as $tipe)
                    <option value="{{ $tipe }}" @selected($selectedTipe === $tipe)>{{ ucwords(str_replace('_', ' ', $tipe)) }}</option>
                @endforeach
            </select>
            @error('tipe_usulan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="dusun_id" class="form-label">Dusun Utama</label>
            <select id="dusun_id" name="dusun_id" class="form-control @error('dusun_id') is-invalid @enderror">
                <option value="">Tidak ada</option>
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
            <label for="dusun_terkait_ids" class="form-label">Dusun Terkait</label>
            <select id="dusun_terkait_ids" name="dusun_terkait_ids[]" class="form-control @error('dusun_terkait_ids') is-invalid @enderror" multiple>
                @foreach ($dusuns as $dusun)
                    <option value="{{ $dusun->id }}" @selected(in_array((int) $dusun->id, $selectedDusunTerkait, true))>{{ $dusun->nama_dusun }}</option>
                @endforeach
            </select>
            @error('dusun_terkait_ids')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
            <input id="nama_kegiatan" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $usulan->nama_kegiatan) }}" class="form-control @error('nama_kegiatan') is-invalid @enderror" maxlength="255" required placeholder="Contoh: Pembangunan jalan tani">
            @error('nama_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="lokasi_kegiatan" class="form-label">Lokasi Kegiatan</label>
            <input id="lokasi_kegiatan" type="text" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan', $usulan->lokasi_kegiatan) }}" class="form-control @error('lokasi_kegiatan') is-invalid @enderror" maxlength="255" placeholder="Contoh: RT.001/RW.001 Dusun Katute">
            @error('lokasi_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="prakiraan_volume" class="form-label">Prakiraan Volume</label>
            <input id="prakiraan_volume" type="number" min="0" step="0.01" name="prakiraan_volume" value="{{ old('prakiraan_volume', $usulan->prakiraan_volume) }}" class="form-control @error('prakiraan_volume') is-invalid @enderror">
            @error('prakiraan_volume')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="satuan" class="form-label">Satuan</label>
            <input id="satuan" type="text" name="satuan" value="{{ old('satuan', $usulan->satuan) }}" class="form-control @error('satuan') is-invalid @enderror" maxlength="50">
            @error('satuan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_lk" class="form-label">Penerima Manfaat LK</label>
            <input id="penerima_manfaat_lk" type="number" min="0" name="penerima_manfaat_lk" value="{{ old('penerima_manfaat_lk', $usulan->penerima_manfaat_lk) }}" class="form-control @error('penerima_manfaat_lk') is-invalid @enderror">
            @error('penerima_manfaat_lk')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_pr" class="form-label">Penerima Manfaat PR</label>
            <input id="penerima_manfaat_pr" type="number" min="0" name="penerima_manfaat_pr" value="{{ old('penerima_manfaat_pr', $usulan->penerima_manfaat_pr) }}" class="form-control @error('penerima_manfaat_pr') is-invalid @enderror">
            @error('penerima_manfaat_pr')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="penerima_manfaat_a_rtm" class="form-label">Penerima Manfaat A-RTM</label>
            <input id="penerima_manfaat_a_rtm" type="number" min="0" name="penerima_manfaat_a_rtm" value="{{ old('penerima_manfaat_a_rtm', $usulan->penerima_manfaat_a_rtm) }}" class="form-control @error('penerima_manfaat_a_rtm') is-invalid @enderror">
            @error('penerima_manfaat_a_rtm')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="kategori_kegiatan" class="form-label">Kategori Kegiatan</label>
            <input id="kategori_kegiatan" type="text" name="kategori_kegiatan" value="{{ old('kategori_kegiatan', $usulan->kategori_kegiatan) }}" class="form-control @error('kategori_kegiatan') is-invalid @enderror" maxlength="100">
            @error('kategori_kegiatan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="sumber_usulan" class="form-label">Sumber Usulan</label>
            <input id="sumber_usulan" type="text" name="sumber_usulan" value="{{ old('sumber_usulan', $usulan->sumber_usulan) }}" class="form-control @error('sumber_usulan') is-invalid @enderror">
            @error('sumber_usulan')<div class="field-error">{{ $message }}</div>@enderror
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
