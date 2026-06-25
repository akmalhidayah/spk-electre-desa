@props([
    'action',
    'tahun' => null,
    'tahunList' => collect(),
    'periode' => null,
])

<form method="GET" action="{{ $action }}" class="filter-bar compact-filter">
    <div class="filter-field input-with-icon">
        <label for="tahun-filter" class="form-label sr-only">Tahun Perencanaan</label>
        <span class="input-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4" /><path d="M3 10h18" /><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /></svg>
        </span>
        <select id="tahun-filter" name="tahun" class="form-control">
            @foreach ($tahunList as $item)
                <option value="{{ $item }}" @selected((string) $tahun === (string) $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-secondary">Terapkan</button>
        @if ($periode?->is_active)
            <span class="badge badge-success">Tahun Aktif</span>
        @endif
        @if ($periode?->perlu_hitung_ulang)
            <span class="badge badge-warning">Perlu Hitung Ulang</span>
        @endif
    </div>
</form>
