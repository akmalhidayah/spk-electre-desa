<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pdfTitle ?? 'Daftar Usulan Pembangunan Diterima per Dusun' }}</title>
    <style>
        @page { margin: 24px 32px 44px; }
        body { background: #ffffff; color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; line-height: 1.4; margin: 0; }
        h1, h2, h3, p { margin: 0; }
        .letterhead { border-bottom: 3px double #111827; margin-bottom: 14px; padding: 0 0 10px; }
        .letterhead-table { border-collapse: collapse; margin: 0; width: 100%; }
        .letterhead-table td { border: 0; padding: 0; vertical-align: middle; }
        .logo-cell { text-align: center; width: 82px; }
        .logo { height: 64px; max-width: 68px; object-fit: contain; }
        .letterhead-title { text-align: center; }
        .letterhead-title .agency { font-size: 13px; font-weight: bold; letter-spacing: .04em; text-transform: uppercase; }
        .letterhead-title .village { font-size: 18px; font-weight: bold; letter-spacing: .06em; line-height: 1.3; text-transform: uppercase; }
        .letterhead-title .address { color: #334155; font-size: 10px; line-height: 1.45; margin-top: 3px; }
        .document-title { margin: 14px 0 12px; text-align: center; }
        .document-title h1 { color: #111827; font-size: 14px; letter-spacing: .04em; line-height: 1.45; text-transform: uppercase; }
        .document-title h2 { color: #0f766e; font-size: 11px; margin-top: 4px; }
        .summary { margin-bottom: 10px; }
        .summary table { border-collapse: collapse; width: 100%; }
        .summary td { border: 0; padding: 2px 0; }
        .summary td:first-child { font-weight: bold; width: 118px; }
        table.usulan-table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        .usulan-table th, .usulan-table td { border: 1px solid #94a3b8; padding: 5px 6px; vertical-align: top; }
        .usulan-table th { background: #e2e8f0; color: #0f172a; font-size: 9px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .usulan-table td { font-size: 9px; }
        .usulan-table tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .col-no { width: 28px; }
        .col-volume { width: 58px; }
        .col-satuan { width: 54px; }
        .col-benefit { width: 46px; }
        .signature { margin-top: 34px; margin-left: auto; text-align: center; width: 240px; }
        .signature .line { margin-top: 54px; }
        .footer { border-top: 1px solid #cbd5e1; bottom: 18px; color: #64748b; font-size: 8px; left: 32px; padding-top: 6px; position: fixed; right: 32px; text-align: center; }
    </style>
</head>
<body>
    @php
        $logoKiriPath = public_path('images/logo-kiri.png');
        $logoKananPath = public_path('images/logo-kanan.png');
        $logoKiri = file_exists($logoKiriPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKiriPath)) : null;
        $logoKanan = file_exists($logoKananPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKananPath)) : null;
        $bulanIndonesia = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $tanggalSurat = now()->format('d').' '.$bulanIndonesia[(int) now()->format('n')].' '.now()->format('Y');
    @endphp

    <div class="letterhead">
        <table class="letterhead-table">
            <tr>
                <td class="logo-cell">
                    @if ($logoKiri)
                        <img src="{{ $logoKiri }}" class="logo" alt="Logo kiri">
                    @endif
                </td>
                <td class="letterhead-title">
                    <p class="agency">Pemerintah Kabupaten Sinjai</p>
                    <p class="agency">Kecamatan Sinjai Borong</p>
                    <p class="village">Desa Barambang</p>
                    <p class="address">Alamat: Desa Barambang, Kecamatan Sinjai Borong, Kabupaten Sinjai, Sulawesi Selatan</p>
                </td>
                <td class="logo-cell">
                    @if ($logoKanan)
                        <img src="{{ $logoKanan }}" class="logo" alt="Logo kanan">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="document-title">
        <h1>Daftar Usulan Pembangunan Diterima</h1>
        <h2>{{ $dusun->nama_dusun }} Tahun {{ $calculation->tahun }}</h2>
    </div>

    <div class="summary">
        <table>
            <tr><td>Desa</td><td>: Barambang</td></tr>
            <tr><td>Dusun</td><td>: {{ $dusun->nama_dusun }}</td></tr>
            <tr><td>Kepala Dusun</td><td>: {{ $kepalaDusunName ?? $kepalaDusun?->name ?? '-' }}</td></tr>
            <tr><td>Tahun</td><td>: {{ $calculation->tahun }}</td></tr>
            <tr><td>Total Usulan</td><td>: {{ $usulans->count() }} usulan diterima</td></tr>
        </table>
    </div>

    <table class="usulan-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th>Gagasan/Kegiatan</th>
                <th>Lokasi Kegiatan</th>
                <th class="col-volume">Volume</th>
                <th class="col-satuan">Satuan</th>
                <th class="col-benefit">LK</th>
                <th class="col-benefit">PR</th>
                <th class="col-benefit">A-RTM</th>
                <th>Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usulans as $usulan)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $usulan->nama_kegiatan }}</td>
                    <td>{{ $usulan->lokasi_kegiatan ?: $dusun->nama_dusun }}</td>
                    <td class="text-center nowrap">
                        {{ $usulan->prakiraan_volume !== null ? rtrim(rtrim(number_format((float) $usulan->prakiraan_volume, 2, ',', '.'), '0'), ',') : '-' }}
                    </td>
                    <td class="text-center">{{ $usulan->satuan ?: '-' }}</td>
                    <td class="text-center">{{ $usulan->penerima_manfaat_lk !== null ? number_format($usulan->penerima_manfaat_lk, 0, ',', '.') : '-' }}</td>
                    <td class="text-center">{{ $usulan->penerima_manfaat_pr !== null ? number_format($usulan->penerima_manfaat_pr, 0, ',', '.') : '-' }}</td>
                    <td class="text-center">{{ $usulan->penerima_manfaat_a_rtm !== null ? number_format($usulan->penerima_manfaat_a_rtm, 0, ',', '.') : '-' }}</td>
                    <td>{{ $usulan->kategori_kegiatan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Belum ada usulan pembangunan diterima untuk dusun ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <p>Barambang, {{ $tanggalSurat }}</p>
        <p>{{ $dusun->nama_dusun }}</p>
        <p>Kepala Dusun</p>
        <p class="line"><strong>{{ $kepalaDusunName ?? $kepalaDusun?->name ?? '................................' }}</strong></p>
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Daftar usulan pembangunan diterima {{ $dusun->nama_dusun }}
    </div>
</body>
</html>
