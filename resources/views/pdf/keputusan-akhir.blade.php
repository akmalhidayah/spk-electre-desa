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
        .identity td:nth-child(1),
        .identity td:nth-child(3) { font-weight: bold; width: 21%; }
        .identity td:nth-child(2),
        .identity td:nth-child(4) { width: 29%; }
        .decision { background: #f0fdfa; border: 1px solid #99f6e4; color: #134e4a; margin-top: 10px; padding: 12px; }
        .decision strong { font-size: 14px; }
        .note { background: #f8fafc; border: 1px solid #dbe3ec; margin-top: 8px; padding: 10px; }
        .ranking th:nth-child(1), .ranking td:nth-child(1) { text-align: center; width: 54px; }
        .ranking th:nth-child(2), .ranking td:nth-child(2) { text-align: center; width: 86px; }
        .ranking th:nth-child(4), .ranking td:nth-child(4) { text-align: center; width: 82px; }
        .ranking th:nth-child(5), .ranking td:nth-child(5) { width: 96px; }
        .selected td { background: #ecfdf5; font-weight: bold; }
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
        .signature { margin-left: auto; margin-top: 36px; text-align: center; width: 230px; }
        .signature .line { margin-top: 54px; }
        .signature-image { height: 54px; margin: 10px auto 4px; max-width: 190px; object-fit: contain; }
        .signature .line.with-image { margin-top: 6px; }
        .footer { border-top: 1px solid #cbd5e1; bottom: 18px; color: #64748b; font-size: 9px; left: 32px; padding-top: 6px; position: fixed; right: 32px; text-align: center; }
    </style>
</head>
<body>
    @php
        $logoKiriPath = public_path('images/logo-kiri.png');
        $logoKananPath = public_path('images/logo-kanan.png');
        $logoKiri = file_exists($logoKiriPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKiriPath)) : null;
        $logoKanan = file_exists($logoKananPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoKananPath)) : null;
        $bulanIndonesia = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $tanggalKeputusan = $keputusan->tanggal_keputusan;
        $tanggalSurat = $tanggalKeputusan
            ? $tanggalKeputusan->format('d').' '.$bulanIndonesia[(int) $tanggalKeputusan->format('n')].' '.$tanggalKeputusan->format('Y')
            : now()->format('d').' '.$bulanIndonesia[(int) now()->format('n')].' '.now()->format('Y');
        $tahunDokumen = $keputusan->tahun ?? $calculation?->tahun ?? '-';
        $namaKepalaDesa = $kepalaDesaName ?? $keputusan->penetap?->name ?? $keputusan->decider?->name ?? '-';
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
        <h1>Laporan Keputusan Prioritas Pembangunan Antar Dusun</h1>
        <h2>Nomor: {{ $keputusan->nomor_keputusan ?: '-' }}</h2>
    </div>

    <div class="section">
        <h3>Identitas Penetapan</h3>
        <table class="identity">
            <tr>
                <td>Nomor Keputusan</td>
                <td>{{ $keputusan->nomor_keputusan ?: '-' }}</td>
                <td>Tahun</td>
                <td>{{ $tahunDokumen }}</td>
            </tr>
            <tr>
                <td>Tanggal Penetapan</td>
                <td>{{ $tanggalSurat }}</td>
                <td>Ditetapkan Oleh</td>
                <td>{{ $namaKepalaDesa }}</td>
            </tr>
            <tr>
                <td>Dasar Penilaian</td>
                <td>{{ $calculation?->kode_perhitungan ?? '-' }}</td>
                <td>Jumlah Dusun Dinilai</td>
                <td>{{ $calculation?->total_alternatif ?? $results->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Penetapan Keputusan</h3>
        <p class="note">
            Berdasarkan hasil penilaian prioritas pembangunan tahun {{ $tahunDokumen }}, data usulan pembangunan yang telah diterima, serta pertimbangan Pemerintah Desa Barambang dalam musyawarah, maka ditetapkan dusun prioritas pembangunan sebagai berikut.
        </p>
        <div class="decision">
            <strong>{{ $keputusan->dusun?->nama_dusun ?? '-' }}</strong>
            ({{ $keputusan->dusun?->kode_alternatif ?? '-' }}) ditetapkan sebagai prioritas pembangunan Desa Barambang Tahun {{ $tahunDokumen }}.
        </div>
        @if ($keputusan->dasar_pertimbangan)
            <p class="note"><strong>Pertimbangan Tambahan</strong><br>{{ $keputusan->dasar_pertimbangan }}</p>
        @endif
        @if ($keputusan->catatan_keputusan)
            <p class="note"><strong>Catatan Keputusan</strong><br>{{ $keputusan->catatan_keputusan }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Urutan Rekomendasi</h3>
        <table class="ranking">
            <thead>
                <tr><th>Urutan</th><th>Kode Dusun</th><th>Nama Dusun</th><th>Nilai Akhir</th><th>Keterangan Prioritas</th></tr>
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
        <h3>Dasar Penilaian</h3>
        <table>
            <thead><tr><th>Kode</th><th>Dasar Penilaian</th><th>Bobot Penilaian</th></tr></thead>
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

    <div class="section page-break">
        <h3>Lampiran Daftar Usulan Pembangunan Diterima Tahun {{ $tahunDokumen }}</h3>
        <p class="appendix-note">Lampiran ini berisi seluruh usulan pembangunan berstatus diterima pada tahun keputusan.</p>
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
                    <tr><td colspan="10" class="text-center">Belum ada usulan pembangunan diterima pada tahun keputusan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature">
        <p>Barambang, {{ $tanggalSurat }}</p>
        <p>Kepala Desa Barambang</p>
        @if ($keputusan->tanda_tangan)
            <img src="{{ $keputusan->tanda_tangan }}" class="signature-image" alt="Tanda tangan kepala desa">
        @endif
        <p class="line {{ $keputusan->tanda_tangan ? 'with-image' : '' }}"><strong>{{ $namaKepalaDesa !== '-' ? $namaKepalaDesa : '................................' }}</strong></p>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} | Dokumen keputusan prioritas pembangunan Desa Barambang
    </div>
</body>
</html>
