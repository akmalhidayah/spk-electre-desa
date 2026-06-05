<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($isSelf ?? false)
        <div class="alert alert-warning">
            Anda sedang mengedit akun yang sedang digunakan. Status aktif akun ini tidak dapat dinonaktifkan dari form.
        </div>
    @endif

    <div class="form-grid">
        <div class="form-group">
            <label for="name" class="form-label">Nama User <span class="required">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name', $userData->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="150" required placeholder="Nama lengkap">
            @error('name')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email <span class="required">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email', $userData->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="150" required placeholder="user@example.com">
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="role" class="form-label">Role <span class="required">*</span></label>
            <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required data-role-select>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $userData->role) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('role')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" data-dusun-field>
            <label for="dusun_id" class="form-label">Dusun <span class="required role-required-marker">*</span></label>
            <select id="dusun_id" name="dusun_id" class="form-control @error('dusun_id') is-invalid @enderror" data-dusun-select>
                <option value="">Pilih dusun</option>
                @foreach ($dusuns as $dusun)
                    <option value="{{ $dusun->id }}" @selected((string) old('dusun_id', $userData->dusun_id) === (string) $dusun->id)>
                        {{ $dusun->kode_alternatif ? $dusun->kode_alternatif.' - ' : '' }}{{ $dusun->nama_dusun }}
                    </option>
                @endforeach
            </select>
            <small class="muted">Wajib untuk role kepala dusun.</small>
            @error('dusun_id')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                {{ ($isEdit ?? false) ? 'Password Baru' : 'Password' }}
                @unless ($isEdit ?? false)<span class="required">*</span>@endunless
            </label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" {{ ($isEdit ?? false) ? '' : 'required' }} placeholder="{{ ($isEdit ?? false) ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
            @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">
                {{ ($isEdit ?? false) ? 'Konfirmasi Password Baru' : 'Konfirmasi Password' }}
                @unless ($isEdit ?? false)<span class="required">*</span>@endunless
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" {{ ($isEdit ?? false) ? '' : 'required' }} placeholder="Ulangi password">
        </div>

        <div class="form-group form-group-full">
            <label class="form-label">Status User</label>
            @if ($isSelf ?? false)
                <input type="hidden" name="is_active" value="1">
            @endif
            <label class="check-row">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $userData->is_active ?? true)) @disabled($isSelf ?? false)>
                <span>Aktif dan dapat login ke sistem</span>
            </label>
            @error('is_active')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Kembali</a>
        <button type="submit" class="btn btn-primary btn-auto" data-loading-text="Menyimpan...">{{ $submitLabel }}</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var roleSelect = document.querySelector('[data-role-select]');
        var dusunField = document.querySelector('[data-dusun-field]');
        var dusunSelect = document.querySelector('[data-dusun-select]');

        function syncDusunField() {
            if (!roleSelect || !dusunField || !dusunSelect) {
                return;
            }

            var isKepalaDusun = roleSelect.value === 'kepala_dusun';
            dusunField.style.display = isKepalaDusun ? '' : 'none';
            dusunSelect.required = isKepalaDusun;
            dusunSelect.disabled = !isKepalaDusun;
        }

        syncDusunField();
        roleSelect && roleSelect.addEventListener('change', syncDusunField);
    });
</script>
