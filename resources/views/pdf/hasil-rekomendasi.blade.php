<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pdfTitle ?? 'Laporan Hasil Rekomendasi' }}</title>
    <style>
        @page { margin: 24px 32px 44px; }
        body { background: #ffffff; color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.5; margin: 0; }
        h1, h2, h3, p { margin: 0; }
        .letterhead { border-bottom: 3px double #111827; margin-bottom: 18px; padding: 0 0 10px; }
        .letterhead-table { border-collapse: collapse; margin: 0; width: 100%; }
        .letterhead-table td { border: 0; padding: 0; vertical-align: middle; }
        .logo-cell { text-align: center; width: 82px; }
        .logo { height: 64px; max-width: 68px; object-fit: contain; }
        .letterhead-title { text-align: center; }
        .letterhead-title .agency { font-size: 13px; font-weight: bold; letter-spacing: .04em; text-transform: uppercase; }
        .letterhead-title .village { font-size: 18px; font-weight: bold; letter-spacing: .06em; line-height: 1.3; text-transform: uppercase; }
        .letterhead-title .address { color: #334155; font-size: 10px; line-height: 1.45; margin-top: 3px; }
        .document-title { margin: 18px 0 16px; text-align: center; }
        .document-title h1 { color: #111827; font-size: 15px; letter-spacing: .04em; line-height: 1.45; text-transform: uppercase; }
        .document-title h2 { color: #0f766e; font-size: 12px; margin-top: 5px; }
        .section { margin-top: 14px; }
        .section h3 { background: #f1f5f9; border-left: 4px solid #0f766e; color: #0f172a; font-size: 11px; letter-spacing: .04em; padding: 7px 10px; text-transform: uppercase; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 8px; vertical-align: top; }
        th { background: #e2e8f0; color: #0f172a; font-weight: bold; text-align: left; }
        tr:nth-child(even) td { background: #f8fafc; }
        .identity td:nth-child(1),
        .identity td:nth-child(3) { font-weight: bold; width: 21%; }
        .identity td:nth-child(2),
        .identity td:nth-child(4) { width: 29%; }
        .note { background: #f8fafc; border: 1px solid #dbe3ec; margin-top: 8px; padding: 10px; }
        .priority { background: #f0fdfa; border: 1px solid #99f6e4; color: #134e4a; margin-top: 10px; padding: 11px; }
        .ranking-table th:nth-child(1), .ranking-table td:nth-child(1) { text-align: center; width: 54px; }
        .ranking-table th:nth-child(2), .ranking-table td:nth-child(2) { text-align: center; width: 86px; }
        .ranking-table th:nth-child(4), .ranking-table td:nth-child(4) { text-align: center; width: 82px; }
        .ranking-table th:nth-child(5), .ranking-table td:nth-child(5) { width: 96px; }
        .page-break { page-break-before: always; }
        .appendix-note { color: #475569; font-size: 10px; margin-top: 8px; }
        .appendix-table th, .appendix-table td { font-size: 8px; padding: 4px 5px; }
        .appendix-table th { text-align: center; }
        .appendix-table .text-center { text-align: center; }
        .appendix-table .nowrap { white-space: nowrap; }
        .appendix-table .col-no { width: 24px; }
        .appendix-table .col-sdgs { width: 42px; }
        .appendix-table .col-volume { width: 50px; }
        .appendix-table .col-satuan { width: 44px; }
        .appendix-table .col-benefit { width: 38px; }
        .signature { margin-top: 42px; margin-left: auto; text-align: center; width: 220px; }
        .signature .line { margin-top: 56px; }
        .footer { border-top: 1px solid #cbd5e1; bottom: 18px; color: #64748b; font-size: 9px; left: 32px; position: fixed; right: 32px; text-align: center; padding-top: 6px; }
    </style>
</head>
<body>
    @php
        $topResult = $results->first();
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
        <h1>Laporan Hasil Rekomendasi Prioritas Pembangunan Antar Dusun</h1>
        <h2>Bahan Pertimbangan Penentuan Prioritas Pembangunan Desa</h2>
    </div>

    <div class="section">
        <h3>Identitas Penilaian</h3>
        <table class="identity">
            <tr>
                <td>Nomor Dokumen</td>
                <td>{{ $calculation->kode_perhitungan ?? '-' }}</td>
                <td>Tahun</td>
                <td>{{ $calculation->tahun ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Penyusunan</td>
                <td>{{ $calculation->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                <td>Disusun Oleh</td>
                <td>{{ $calculation->calculator?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jumlah Dusun Dinilai</td>
                <td>{{ $calculation->total_alternatif ?? 0 }}</td>
                <td>Jumlah Dasar Penilaian</td>
                <td>{{ $calculation->total_kriteria ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Ringkasan Rekomendasi</h3>
        <p class="note">Berdasarkan hasil penilaian terhadap data usulan pembangunan, diperoleh urutan dusun yang direkomendasikan sebagai prioritas pembangunan sebagai berikut.</p>
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Kode Dusun</th>
                    <th>Nama Dusun</th>
                    <th>Nilai Akhir</th>
                    <th>Keterangan Prioritas</th>
                    <th>Catatan</th>
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
                        <td>
                            @if ((int) ($result->ranking ?? 0) === 1)
                                Rekomendasi utama berdasarkan hasil penilaian.
                            @else
                                Urutan ke-{{ $result->ranking ?? '-' }} berdasarkan hasil penilaian.
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Data hasil ranking tidak tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($topResult)
            <div class="priority">
                Dusun yang menjadi prioritas utama pembangunan adalah <strong>{{ $topResult->dusun?->nama_dusun ?? '-' }}</strong>
                dengan nilai akhir <strong>{{ $topResult->skor_dominasi }}</strong>.
            </div>
        @endif
    </div>

    <div class="section">
        <h3>Dasar Penilaian</h3>
        <table>
            <thead><tr><th>Kode</th><th>Dasar Penilaian</th><th>Bobot Penilaian</th></tr></thead>
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
        <p class="note">Dokumen ini digunakan sebagai bahan pertimbangan dalam musyawarah dan pengambilan keputusan pembangunan desa. Penetapan akhir tetap dilakukan oleh Pemerintah Desa berdasarkan kebutuhan dan hasil musyawarah.</p>
    </div>

    <div class="section page-break">
        <h3>Lampiran Daftar Usulan Pembangunan Diterima Tahun {{ $calculation->tahun ?? '-' }}</h3>
        <p class="appendix-note">Lampiran ini berisi usulan pembangunan berstatus diterima pada tahun yang sama dengan hasil rekomendasi.</p>
        <table class="appendix-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Gagasan/Kegiatan</th>
                    <th>Lokasi Kegiatan</th>
                    <th class="col-sdgs">SDGs Ke</th>
                    <th class="col-volume">Volume</th>
                    <th class="col-satuan">Satuan</th>
                    <th class="col-benefit">LK</th>
                    <th class="col-benefit">PR</th>
                    <th class="col-benefit">A-RTM</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($acceptedUsulans ?? collect()) as $usulan)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $usulan->nama_kegiatan }}</td>
                        <td>{{ $usulan->lokasi_kegiatan ?: ($usulan->dusun?->nama_dusun ?? 'Desa Barambang') }}</td>
                        <td class="text-center">{{ $usulan->sdgs_ke ?: '-' }}</td>
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
                    <tr><td colspan="10" class="text-center">Belum ada usulan pembangunan diterima pada tahun ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature">
        <p>Barambang, {{ $tanggalSurat }}</p>
        <p>Kepala Desa Barambang</p>
        <p class="line"><strong>{{ $kepalaDesaName ?? '................................' }}</strong></p>
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Dokumen rekomendasi prioritas pembangunan Desa Barambang
    </div>
</body>
</html>
