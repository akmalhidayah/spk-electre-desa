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

        <div class="form-group form-group-full signature-field">
            <label class="form-label">Tanda Tangan Kepala Desa</label>
            <input type="hidden" id="tanda_tangan" name="tanda_tangan" value="{{ old('tanda_tangan') }}">
            <div class="signature-control">
                <div class="signature-preview" data-signature-preview>
                    @if (old('tanda_tangan'))
                        <img src="{{ old('tanda_tangan') }}" alt="Pratinjau tanda tangan">
                    @else
                        <span>Belum ada tanda tangan</span>
                    @endif
                </div>
                <div class="signature-actions">
                    <button type="button" class="btn btn-secondary btn-auto" data-open-signature-modal>
                        Buat Tanda Tangan
                    </button>
                    <button type="button" class="btn btn-light btn-auto" data-clear-saved-signature>
                        Hapus
                    </button>
                </div>
            </div>
            @error('tanda_tangan')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('kepala-desa.hasil-rekomendasi.show', $calculation) }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Menyimpan...">
            Simpan Keputusan Akhir
        </button>
    </div>
</form>

<div class="modal-backdrop" data-signature-modal hidden>
    <div class="modal-card signature-modal-card" role="dialog" aria-modal="true" aria-labelledby="signatureModalTitle">
        <div class="modal-head">
            <div>
                <h3 id="signatureModalTitle">Tanda Tangan Kepala Desa</h3>
                <p>Gambar tanda tangan pada area di bawah ini.</p>
            </div>
            <button type="button" class="icon-button" data-close-signature-modal aria-label="Tutup modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>
        <div class="signature-pad-wrap">
            <canvas id="signatureCanvas" class="signature-canvas" width="720" height="260"></canvas>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" data-clear-signature-canvas>Bersihkan</button>
            <button type="button" class="btn btn-light" data-close-signature-modal>Batal</button>
            <button type="button" class="btn btn-primary" data-save-signature-canvas>Simpan Tanda Tangan</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.querySelector('[data-signature-modal]');
        var canvas = document.getElementById('signatureCanvas');
        var input = document.getElementById('tanda_tangan');
        var preview = document.querySelector('[data-signature-preview]');
        var openButton = document.querySelector('[data-open-signature-modal]');

        if (!modal || !canvas || !input || !preview || !openButton) {
            return;
        }

        var context = canvas.getContext('2d');
        var drawing = false;
        var hasInk = false;

        function prepareCanvas() {
            context.lineWidth = 3;
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.strokeStyle = '#0f172a';
        }

        function position(event) {
            var rect = canvas.getBoundingClientRect();
            var point = event.touches ? event.touches[0] : event;

            return {
                x: (point.clientX - rect.left) * (canvas.width / rect.width),
                y: (point.clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDraw(event) {
            event.preventDefault();
            drawing = true;
            hasInk = true;
            var point = position(event);
            context.beginPath();
            context.moveTo(point.x, point.y);
        }

        function draw(event) {
            if (!drawing) {
                return;
            }

            event.preventDefault();
            var point = position(event);
            context.lineTo(point.x, point.y);
            context.stroke();
        }

        function endDraw() {
            drawing = false;
        }

        function clearCanvas() {
            context.clearRect(0, 0, canvas.width, canvas.height);
            hasInk = false;
            prepareCanvas();
        }

        function updatePreview(dataUrl) {
            preview.innerHTML = '';

            if (!dataUrl) {
                var empty = document.createElement('span');
                empty.textContent = 'Belum ada tanda tangan';
                preview.appendChild(empty);
                return;
            }

            var image = document.createElement('img');
            image.src = dataUrl;
            image.alt = 'Pratinjau tanda tangan';
            preview.appendChild(image);
        }

        prepareCanvas();

        openButton.addEventListener('click', function () {
            modal.hidden = false;
            document.body.classList.add('modal-open');
            clearCanvas();
        });

        modal.querySelectorAll('[data-close-signature-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
            }
        });

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', endDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', endDraw);

        document.querySelector('[data-clear-signature-canvas]').addEventListener('click', clearCanvas);

        document.querySelector('[data-save-signature-canvas]').addEventListener('click', function () {
            if (!hasInk) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanda tangan masih kosong',
                        text: 'Silakan gambar tanda tangan terlebih dahulu.',
                        confirmButtonColor: '#047857'
                    });
                } else {
                    window.alert('Silakan gambar tanda tangan terlebih dahulu.');
                }

                return;
            }

            var dataUrl = canvas.toDataURL('image/png');
            input.value = dataUrl;
            updatePreview(dataUrl);
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        });

        document.querySelector('[data-clear-saved-signature]').addEventListener('click', function () {
            input.value = '';
            updatePreview('');
        });
    });
</script>
