<form
    method="POST"
    action="{{ route('kepala-desa.keputusan-akhir.store') }}"
    class="form-stack js-confirm"
    data-title="Simpan Keputusan Akhir?"
    data-text="Keputusan akhir akan disimpan sebagai dokumen keputusan pemerintah desa."
    data-icon="question"
    data-confirm-button="Ya, Simpan"
>
    @csrf
    <input type="hidden" name="electre_calculation_id" value="{{ $calculation->id }}">

    <div class="form-grid">
        <div class="form-group form-group-full">
            <label for="dusun_id" class="form-label">Dusun Prioritas <span class="required">*</span></label>
            <select id="dusun_id" name="dusun_id" class="form-control @error('dusun_id') is-invalid @enderror" required>
                @foreach ($results as $result)
                    <option value="{{ $result->dusun_id }}" @selected((string) old('dusun_id', $results->first()?->dusun_id) === (string) $result->dusun_id)>
                        Ranking {{ $result->ranking }} - {{ $result->dusun?->nama_dusun ?? '-' }} - Skor dominasi {{ $result->skor_dominasi }}
                    </option>
                @endforeach
            </select>
            @error('dusun_id')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="nomor_keputusan" class="form-label">Nomor Keputusan</label>
            <input id="nomor_keputusan" type="text" name="nomor_keputusan" value="{{ old('nomor_keputusan') }}" class="form-control @error('nomor_keputusan') is-invalid @enderror" maxlength="100" placeholder="Contoh: 01/KPTS/DB/{{ $calculation->tahun }}">
            @error('nomor_keputusan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="tanggal_keputusan" class="form-label">Tanggal Keputusan <span class="required">*</span></label>
            <input id="tanggal_keputusan" type="date" name="tanggal_keputusan" value="{{ old('tanggal_keputusan', now()->toDateString()) }}" class="form-control @error('tanggal_keputusan') is-invalid @enderror" required>
            @error('tanggal_keputusan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status <span class="required">*</span></label>
            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                <option value="draft" @selected(old('status', 'ditetapkan') === 'draft')>Draft</option>
                <option value="ditetapkan" @selected(old('status', 'ditetapkan') === 'ditetapkan')>Ditetapkan</option>
            </select>
            @error('status')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="dasar_pertimbangan" class="form-label">Dasar Pertimbangan</label>
            <textarea id="dasar_pertimbangan" name="dasar_pertimbangan" rows="4" class="form-control @error('dasar_pertimbangan') is-invalid @enderror" placeholder="Tuliskan dasar pertimbangan keputusan, misalnya hasil ELECTRE, urgensi pembangunan, dan hasil musyawarah desa.">{{ old('dasar_pertimbangan') }}</textarea>
            @error('dasar_pertimbangan')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group form-group-full">
            <label for="catatan_keputusan" class="form-label">Catatan Keputusan</label>
            <textarea id="catatan_keputusan" name="catatan_keputusan" rows="4" class="form-control @error('catatan_keputusan') is-invalid @enderror" placeholder="Catatan tambahan keputusan akhir.">{{ old('catatan_keputusan') }}</textarea>
            @error('catatan_keputusan')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Menyimpan...">
            Simpan Keputusan Akhir
        </button>
    </div>
</form>
