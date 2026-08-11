@props(['summary'])

<section class="stat-grid">
    @foreach ([
        'Pagu Anggaran' => $summary['pagu'],
        'Anggaran Sudah Ditetapkan' => $summary['total_ditetapkan'],
        'Sisa Pagu' => $summary['sisa_pagu'],
    ] as $label => $value)
        <article class="stat-card stat-solid stat-teal">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value stat-value-code">{{ $value !== null ? 'Rp '.number_format((float) $value, 0, ',', '.') : '-' }}</div>
        </article>
    @endforeach
    <article class="stat-card stat-solid stat-indigo">
        <div class="stat-label">Program Ditetapkan</div>
        <div class="stat-value">{{ number_format($summary['jumlah_program_ditetapkan']) }}</div>
    </article>
</section>
