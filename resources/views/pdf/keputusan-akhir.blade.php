<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penetapan Hasil Prioritas Pembangunan</title>
    <style>
        @page { margin: 24px 32px 44px; }
        body { color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.5; margin: 0; }
        h1, h2, h3, p { margin: 0; }
        .letterhead { border-bottom: 3px double #111827; margin-bottom: 18px; padding-bottom: 10px; }
        .letterhead-table { border-collapse: collapse; width: 100%; }
        .letterhead-table td { border: 0; padding: 0; vertical-align: middle; }
        .logo-cell { text-align: center; width: 82px; }
        .logo { height: 64px; max-width: 68px; object-fit: contain; }
        .letterhead-title { text-align: center; }
        .agency { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .village { font-size: 18px; font-weight: bold; letter-spacing: .06em; text-transform: uppercase; }
        .address { color: #334155; font-size: 10px; margin-top: 3px; }
        .document-title { margin: 18px 0 16px; text-align: center; }
        .document-title h1 { font-size: 15px; letter-spacing: .04em; text-transform: uppercase; }
        .document-title h2 { font-size: 11px; margin-top: 5px; }
        .section { margin-top: 14px; }
        .section h3 { background: #f1f5f9; border-left: 4px solid #0f766e; font-size: 11px; padding: 7px 10px; text-transform: uppercase; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 8px; vertical-align: top; }
        th { background: #e2e8f0; text-align: left; }
        .identity td:first-child { font-weight: bold; width: 33%; }
        .decision { background: #f0fdfa; border: 1px solid #99f6e4; margin-top: 10px; padding: 12px; }
        .decision strong { color: #134e4a; font-size: 14px; }
        .note { background: #f8fafc; border: 1px solid #dbe3ec; margin-top: 8px; padding: 10px; white-space: pre-line; }
        .ranking th:nth-child(1), .ranking td:nth-child(1) { text-align: center; width: 55px; }
        .ranking th:nth-child(2), .ranking td:nth-child(2) { text-align: center; width: 85px; }
        .ranking th:nth-child(4), .ranking td:nth-child(4) { text-align: center; width: 85px; }
        .selected td { background: #ecfdf5; font-weight: bold; }
        .signature { margin-left: auto; margin-top: 36px; text-align: center; width: 230px; }
        .signature .line { margin-top: 54px; }
        .footer { border-top: 1px solid #cbd5e1; bottom: 18px; color: #64748b; font-size: 9px; left: 32px; padding-top: 6px; position: fixed; right: 32px; text-align: center; }
    </style>
</head>
<body>
    @php
        $logoKiriPath = public_path('images/logo-kiri.png');
        $logoKananPath = public_path('images/logo-kanan.png');
        $logoKiri = file_exists($logoKiriPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKiriPath)) : null;
        $logoKanan = file_exists($logoKananPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKananPath)) : null;
    @endphp

    <div class="letterhead">
        <table class="letterhead-table">
            <tr>
                <td class="logo-cell">
                    @if ($logoKiri)<img src="{{ $logoKiri }}" class="logo" alt="Logo kiri">@endif
                </td>
                <td class="letterhead-title">
                    <p class="agency">Pemerintah Kabupaten Sinjai</p>
                    <p class="agency">Kecamatan Sinjai Borong</p>
                    <p class="village">Desa Barambang</p>
                    <p class="address">Desa Barambang, Kecamatan Sinjai Borong, Kabupaten Sinjai, Sulawesi Selatan</p>
                </td>
                <td class="logo-cell">
                    @if ($logoKanan)<img src="{{ $logoKanan }}" class="logo" alt="Logo kanan">@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="document-title">
        <h1>Laporan Penetapan Hasil Prioritas Pembangunan Antar Dusun</h1>
        <h2>Nomor: {{ $keputusan->nomor_keputusan ?: '-' }}</h2>
    </div>

    <div class="section">
        <h3>Identitas Penetapan</h3>
        <table class="identity">
            <tr><td>Kode Perhitungan</td><td>{{ $calculation?->kode_perhitungan ?? '-' }}</td></tr>
            <tr><td>Judul Perhitungan</td><td>{{ $calculation?->judul ?? '-' }}</td></tr>
            <tr><td>Tahun</td><td>{{ $keputusan->tahun ?? $calculation?->tahun ?? '-' }}</td></tr>
            <tr><td>Tanggal Keputusan</td><td>{{ $keputusan->tanggal_keputusan?->translatedFormat('d F Y') ?? '-' }}</td></tr>
            <tr><td>Status</td><td>{{ ucfirst($keputusan->status) }}</td></tr>
            <tr><td>Ditetapkan Oleh</td><td>{{ $keputusan->penetap?->name ?? $keputusan->decider?->name ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Hasil Penetapan</h3>
        <div class="decision">
            Dusun yang ditetapkan sebagai prioritas pembangunan adalah<br>
            <strong>{{ $keputusan->dusun?->nama_dusun ?? '-' }}</strong>
            ({{ $keputusan->dusun?->kode_alternatif ?? '-' }}).
        </div>
        <p class="note"><strong>Dasar Pertimbangan</strong><br>{{ $keputusan->dasar_pertimbangan ?: 'Berdasarkan hasil perhitungan metode ELECTRE dan pertimbangan Pemerintah Desa.' }}</p>
        @if ($keputusan->catatan_keputusan)
            <p class="note"><strong>Catatan Keputusan</strong><br>{{ $keputusan->catatan_keputusan }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Hasil Ranking ELECTRE</h3>
        <table class="ranking">
            <thead>
                <tr><th>Ranking</th><th>Kode</th><th>Nama Dusun</th><th>Skor</th><th>Status Prioritas</th></tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr class="{{ $result->dusun_id === $keputusan->dusun_id ? 'selected' : '' }}">
                        <td>{{ $result->ranking }}</td>
                        <td>{{ $result->dusun?->kode_alternatif ?? '-' }}</td>
                        <td>{{ $result->dusun?->nama_dusun ?? '-' }}</td>
                        <td>{{ $result->skor_dominasi }}</td>
                        <td>{{ $result->status_prioritas ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Data hasil ranking tidak tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Kriteria yang Digunakan</h3>
        <table>
            <thead><tr><th>Kode</th><th>Nama Kriteria</th><th>Bobot</th></tr></thead>
            <tbody>
                @forelse ($kriterias as $kriteria)
                    <tr>
                        <td>{{ $kriteria->kode }}</td>
                        <td>{{ $kriteria->nama_kriteria }}</td>
                        <td>{{ number_format((float) $kriteria->bobot, 2, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Data kriteria tidak tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature">
        <p>Barambang, {{ $keputusan->tanggal_keputusan?->translatedFormat('d F Y') ?? '....................' }}</p>
        <p>Kepala Desa Barambang</p>
        <p class="line"><strong>{{ $keputusan->penetap?->name ?? $keputusan->decider?->name ?? '(................................)' }}</strong></p>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} | Sistem Pendukung Keputusan Prioritas Pembangunan Desa
    </div>
</body>
</html>
