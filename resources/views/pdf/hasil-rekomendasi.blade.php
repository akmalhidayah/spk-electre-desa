<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pdfTitle ?? 'Laporan Hasil Rekomendasi' }}</title>
    <style>
        body { background: #ffffff; color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.5; margin: 28px; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 3px solid #047857; margin-bottom: 18px; padding: 4px 0 14px; text-align: center; }
        .header h1 { color: #0f172a; font-size: 16px; letter-spacing: .03em; line-height: 1.35; text-transform: uppercase; }
        .header h2 { color: #047857; font-size: 13px; margin-top: 5px; }
        .header p { color: #475569; font-size: 11px; margin-top: 7px; }
        .section { margin-top: 16px; }
        .section h3 { background: #ecfdf5; border: 1px solid #bbf7d0; color: #065f46; font-size: 12px; padding: 8px 10px; text-transform: uppercase; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 7px 8px; vertical-align: top; }
        th { background: #f8fafc; color: #334155; font-weight: bold; text-align: left; }
        tr:nth-child(even) td { background: #fbfdff; }
        .identity td:first-child { font-weight: bold; width: 33%; }
        .note { background: #f8fafc; border: 1px solid #e2e8f0; margin-top: 8px; padding: 10px; }
        .priority { background: #f5f3ff; border: 1px solid #ddd6fe; color: #3b0764; margin-top: 10px; padding: 11px; }
        .signature { margin-top: 42px; margin-left: auto; text-align: center; width: 220px; }
        .signature .line { margin-top: 56px; }
        .footer { border-top: 1px solid #d1d5db; bottom: 0; color: #64748b; font-size: 9px; left: 28px; position: fixed; right: 28px; text-align: center; padding-top: 6px; }
    </style>
</head>
<body>
    @php
        $topResult = $results->first();
    @endphp

    <div class="header">
        <h1>Laporan Hasil Rekomendasi Prioritas Pembangunan Antar Dusun</h1>
        <h2>Menggunakan Metode ELECTRE</h2>
        <p>Desa Barambang, Kecamatan Sinjai Borong, Kabupaten Sinjai</p>
    </div>

    <div class="section">
        <h3>Identitas Perhitungan</h3>
        <table class="identity">
            <tr><td>Kode Perhitungan</td><td>{{ $calculation->kode_perhitungan ?? '-' }}</td></tr>
            <tr><td>Tahun</td><td>{{ $calculation->tahun ?? '-' }}</td></tr>
            <tr><td>Tanggal Perhitungan</td><td>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
            <tr><td>Dihitung Oleh</td><td>{{ $calculation->calculator?->name ?? '-' }}</td></tr>
            <tr><td>Jumlah Alternatif</td><td>{{ $calculation->total_alternatif ?? 0 }}</td></tr>
            <tr><td>Jumlah Kriteria</td><td>{{ $calculation->total_kriteria ?? 0 }}</td></tr>
            <tr><td>Status</td><td>{{ ucfirst($calculation->status ?? '-') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Ringkasan Rekomendasi</h3>
        <p class="note">Berdasarkan hasil perhitungan menggunakan metode ELECTRE, diperoleh urutan prioritas pembangunan antar dusun sebagai berikut.</p>
        <table>
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Kode Alternatif</th>
                    <th>Nama Dusun</th>
                    <th>Skor Dominasi</th>
                    <th>Status Prioritas</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td>{{ $result->ranking ?? '-' }}</td>
                        <td>{{ $result->dusun?->kode_alternatif ?? '-' }}</td>
                        <td>{{ $result->dusun?->nama_dusun ?? '-' }}</td>
                        <td>{{ $result->skor_dominasi ?? 0 }}</td>
                        <td>{{ $result->status_prioritas ?? '-' }}</td>
                        <td>{{ $result->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Data hasil ranking tidak tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($topResult)
            <div class="priority">
                Dusun yang menjadi prioritas utama pembangunan adalah <strong>{{ $topResult->dusun?->nama_dusun ?? '-' }}</strong>
                dengan skor dominasi <strong>{{ $topResult->skor_dominasi }}</strong>.
            </div>
        @endif
    </div>

    <div class="section">
        <h3>Kriteria yang Digunakan</h3>
        <table>
            <thead><tr><th>Kode</th><th>Nama Kriteria</th><th>Bobot</th></tr></thead>
            <tbody>
                @forelse (($kriterias ?? collect()) as $kriteria)
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

    <div class="section">
        <h3>Catatan</h3>
        <p class="note">Hasil rekomendasi ini digunakan sebagai bahan pertimbangan dalam proses musyawarah dan pengambilan keputusan pembangunan desa. Keputusan akhir tetap berada pada pemerintah desa.</p>
    </div>

    <div class="signature">
        <p>Barambang, ................. 20....</p>
        <p>Kepala Desa Barambang</p>
        <p class="line">(................................)</p>
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Sistem Pendukung Keputusan Prioritas Pembangunan Desa
    </div>
</body>
</html>
