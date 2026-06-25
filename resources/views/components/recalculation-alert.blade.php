@props(['periode' => null])

@if ($periode?->perlu_hitung_ulang)
    <section class="alert alert-warning">
        <strong>Data berubah, perlu hitung ulang.</strong>
        @if ($periode->alasan_hitung_ulang)
            <span>{{ $periode->alasan_hitung_ulang }}</span>
        @endif
    </section>
@endif
