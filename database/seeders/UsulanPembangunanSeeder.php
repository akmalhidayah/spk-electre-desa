<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UsulanPembangunanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $periodes = TahunPerencanaan::whereIn('tahun', [2022, 2023, 2026])
            ->get()
            ->keyBy('tahun');
        $dusuns = Dusun::all()->keyBy('nama_dusun');

        $this->seedItems(
            $this->items2022(),
            $periodes[2022],
            $admin,
            $dusuns,
            'RKP Desa Barambang',
            'Daftar Usulan Non Prioritas Rencana Kerja Pemerintah Desa Tahun Anggaran 2022',
        );
        $this->seedItems(
            $this->items2023(),
            $periodes[2023],
            $admin,
            $dusuns,
            'RKP Desa Barambang',
            'Daftar Usulan Rencana Kerja Pemerintah Desa Tahun Anggaran 2023',
        );
        UsulanPembangunan::where('tahun_perencanaan_id', $periodes[2026]->id)
            ->where('sumber_dokumen', 'RKP/RPJM Desa Barambang')
            ->whereNull('nomor_dokumen')
            ->delete();
        $this->seedItems(
            $this->items2026(),
            $periodes[2026],
            $admin,
            $dusuns,
            'RKP/RPJM Desa Barambang',
            'RKP/RPJM Desa Barambang',
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<string, Dusun>  $dusuns
     */
    private function seedItems(
        array $items,
        TahunPerencanaan $periode,
        ?User $admin,
        Collection $dusuns,
        string $sumberUsulan,
        string $sumberDokumen,
    ): void {
        foreach ($items as $item) {
            $relatedDusunIds = $this->detectDusunIds($item['lokasi'], $dusuns);
            $tipeUsulan = match (true) {
                count($relatedDusunIds) > 1 => UsulanPembangunan::TIPE_LINTAS_DUSUN,
                count($relatedDusunIds) === 1 => UsulanPembangunan::TIPE_DUSUN,
                default => UsulanPembangunan::TIPE_UMUM_DESA,
            };

            $usulan = UsulanPembangunan::updateOrCreate(
                array_filter([
                    'tahun_perencanaan_id' => $periode->id,
                    'nomor_dokumen' => $item['nomor'] ?? null,
                    'nama_kegiatan' => $item['nama'],
                    'lokasi_kegiatan' => $item['lokasi'],
                    'prakiraan_volume' => $item['volume'],
                    'satuan' => $item['satuan'],
                ], fn ($value, $key) => $key !== 'nomor_dokumen' || $value !== null, ARRAY_FILTER_USE_BOTH),
                [
                    'dusun_id' => $tipeUsulan === UsulanPembangunan::TIPE_UMUM_DESA ? null : $relatedDusunIds[0],
                    'user_id' => $admin?->id,
                    'tipe_usulan' => $tipeUsulan,
                    'penerima_manfaat_lk' => $item['lk'],
                    'penerima_manfaat_pr' => $item['pr'],
                    'penerima_manfaat_a_rtm' => $item['artm'],
                    'sdgs_ke' => null,
                    'sumber_usulan' => $sumberUsulan,
                    'kategori_kegiatan' => $item['kategori'],
                    'jumlah_usulan' => 1,
                    'nomor_dokumen' => $item['nomor'] ?? null,
                    'estimasi_anggaran' => $item['anggaran'] ?? null,
                    'deskripsi' => "Usulan {$item['nama']} pada {$item['lokasi']}.",
                    'sumber_dokumen' => $sumberDokumen,
                    'status_usulan' => UsulanPembangunan::STATUS_DITERIMA,
                    'status_pelaksanaan' => 'belum_dilaksanakan',
                ],
            );

            $usulan->dusunsTerkait()->sync($relatedDusunIds);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items2022(): array
    {
        return [
            $this->r('Pembebasan Tanah Pekuburan Umum', 'Desa Barambang', 0.5, 'Hektare', null, null, null, null),
            $this->r('Pembangunan Pasar', 'Desa Barambang', 1, 'Unit', null, null, null, null),
            $this->r('Pelatihan Pembuatan Pupuk Kompos', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Tanaman Bawang Putih', 'Dusun Balang', 2, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Tanaman Apel', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Stroberi', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Kerajinan Tangan', 'Dusun Balang', 1, 'Klp', null, null, null, null),
            $this->r('Pagar TK/Taman Bermain', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Pagar Posyandu', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Pengadaan Strom Babi', 'Dusun Balang', 5, 'Paket', null, null, null, null),
            $this->r('Talud Masjid', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Alat Kasidah Majelis Taklim', 'Dusun Balang', 1, 'Paket', null, null, null, null),
            $this->r('Saluran Irigasi Jalan Batu Barae', 'Dusun Balang', 2000, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi Jalan Batu Barae', 'Dusun Balang', 250, 'Meter', null, null, null, null),
            $this->r('Pengaspalan Jalan Batu Barae', 'Dusun Balang', 2000, 'Meter', null, null, null, null),
            $this->r('Jembatan Gantung Jalan Sungai Apareng', 'Dusun Batu Massompo', 12, 'Meter', null, null, null, null),
            $this->r('Jembatan', 'Dusun Batu Massompo', 12, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi', 'Dusun Batu Massompo', 250, 'Meter', null, null, null, null),
            $this->r('Talud', 'Dusun Batu Massompo', 40, 'Meter (tinggi 4 m)', null, null, null, null),
            $this->r('Saluran Irigasi Apareng Hulu', 'RT.002/RW.001 Dusun Bonto Manai', 500, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi Lembang Caggoe', 'RT.004/RW.002 Dusun Bonto Manai', 1000, 'Meter', null, null, null, null),
            $this->r('Penambahan Tiang Listrik/Jaringan PLN', 'RT.001/RW.001 Dusun Bonto Manai', 30, 'Rumah', null, null, null, null),
            $this->r('Perbaikan Irigasi Belakang SD 242', 'Dusun Bonto Manai', 200, 'Meter', null, null, null, null),
            $this->r('Talud Depan Masjid Haqqul Yakin', 'RT.003/RW.002 Dusun Bonto Manai', 25, 'Meter', null, null, null, null),
            $this->r('Pembangunan Drainase', 'Dusun Bonto Manai', 100, 'Meter', null, null, null, null),
            $this->r('Talud Samping Masjid Darul Aftar', 'RT.002/RW.001 Dusun Katute', 40, 'Meter', null, null, null, null),
            $this->r('Talud PAUD', 'RT.003/RW.002 Dusun Katute', 50, 'Meter', null, null, null, null),
            $this->r('Pagar PAUD', 'Dusun Katute', null, null, null, null, null, null),
            $this->r('Renovasi Sekolah TK', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Tribun Lapangan', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Pembangunan Bendungan Irigasi Air Terjun', 'Dusun Balang/Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Rehabilitasi Hulu Air Bersih', 'Dusun Katute', 1000, 'Meter', null, null, null, null),
            $this->r('Rehabilitasi Pustu Barambang', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Pengadaan Bibit Kopi', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembuatan Empang/Budidaya Ikan Air Tawar', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembinaan TP PKK', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembinaan Lembaga Adat', 'Desa Barambang', 1, 'Klp', null, null, null, null),
            $this->r('Pengadaan Prasarana Rumah Adat', 'Desa Barambang', 1, 'Ls', null, null, null, null),
            $this->r('Pengadaan Alat Musik Sanggar Seni', 'Desa Barambang', 1, 'Set', null, null, null, null),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function items2023(): array
    {
        return [
            $this->r('Pengerasan Jalan Wisata Batu Barae', 'RT.002/RW.001 Dusun Balang', 1800, 'Meter', null, null, null, null),
            $this->r('Rabat Beton Jalan Sungai Bintino', 'RT.004/RW.002 Dusun Balang', 350, 'Meter', null, null, null, null),
            $this->r('Talud Jalan Sungai Apareng', 'RT.001/RW.001 Dusun Balang', 20, 'Meter (tinggi 3 m)', null, null, null, null),
            $this->r('Pelatihan Pembuatan Pupuk Kompos', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Tanaman Bawang Putih', 'Dusun Balang', 2, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Tanaman Apel', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Budidaya Stroberi', 'Dusun Balang', 5, 'Klp', null, null, null, null),
            $this->r('Pelatihan Kerajinan Tangan', 'Dusun Balang', 1, 'Klp', null, null, null, null),
            $this->r('Pagar TK/Taman Bermain', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Pagar Posyandu', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Penampungan WC Posyandu', 'Dusun Balang', 1, 'Unit', null, null, null, null),
            $this->r('Pengadaan Strom Babi', 'Dusun Balang', 5, 'Paket', null, null, null, null),
            $this->r('Talud Masjid', 'Dusun Balang', 15, 'Meter', null, null, null, null),
            $this->r('Perlengkapan Posyandu', 'Dusun Balang', 1, 'Buah', null, null, null, null),
            $this->r('Plafon Posyandu', 'Dusun Balang', 1, 'Unit', null, null, null, null),
            $this->r('Fasilitas Olahraga', 'Dusun Balang', 1, 'Set', null, null, null, null),
            $this->r('Alat Kasidah Majelis Taklim', 'Dusun Balang', 1, 'Paket', null, null, null, null),
            $this->r('Pakaian Majelis Taklim', 'Dusun Balang', 1, 'Paket', null, null, null, null),
            $this->r('Pembentukan Guru Mengaji', 'Dusun Balang', 1, 'Klp', null, null, null, null),
            $this->r('Rabat Beton', 'Dusun Batu Massompo', 300, 'Meter', null, null, null, null),
            $this->r('Jembatan', 'Dusun Batu Massompo', 12, 'Meter', null, null, null, null),
            $this->r('Duwikker', 'Dusun Batu Massompo', 3, 'Buah', null, null, null, null),
            $this->r('Pengerasan Jalan', 'Dusun Batu Massompo', 100, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi', 'Dusun Batu Massompo', 250, 'Meter', null, null, null, null),
            $this->r('Talud', 'Dusun Batu Massompo', 40, 'Meter (tinggi 4 m)', null, null, null, null),
            $this->r('Pagar Wisata', 'Dusun Batu Massompo', 90, 'Meter', null, null, null, null),
            $this->r('Gazebo', 'Dusun Batu Massompo', 8, 'Buah', null, null, null, null),
            $this->r('Sepeda Gantung', 'Dusun Batu Massompo', 1, 'Set', null, null, null, null),
            $this->r('Pengerasan Jalan ke Masjid', 'Dusun Batu Massompo', 400, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi Apareng Hulu', 'RT.002/RW.001 Dusun Bonto Manai', 500, 'Meter', null, null, null, null),
            $this->r('Saluran Irigasi Lembang Caggoe', 'RT.004/RW.002 Dusun Bonto Manai', 1000, 'Meter', null, null, null, null),
            $this->r('Gorong-Gorong', 'RT.004/RW.002 Dusun Bonto Manai', 2, 'Unit', null, null, null, null),
            $this->r('Duwikker', 'RT.004/RW.002 Dusun Bonto Manai', 2, 'Unit', null, null, null, null),
            $this->r('Rabat Beton', 'RT.003/RW.002 Dusun Bonto Manai', 500, 'Meter', null, null, null, null),
            $this->r('Rabat Beton', 'Dusun Bonto Manai', 200, 'Meter', null, null, null, null),
            $this->r('Perlengkapan Posyandu', 'Dusun Bonto Manai', 2, 'Unit', null, null, null, null),
            $this->r('Peningkatan Bangunan PAUD', 'Dusun Bonto Manai', 1, 'Unit', null, null, null, null),
            $this->r('Penambahan Tiang Listrik/Jaringan PLN', 'RT.001/RW.001 Dusun Bonto Manai', 30, 'Rumah', null, null, null, null),
            $this->r('Perbaikan Irigasi Belakang SD 242', 'Dusun Bonto Manai', 200, 'Meter', null, null, null, null),
            $this->r('Pembangunan Lanjutan Jembatan Liu Sirie', 'RT.001 Dusun Bonto Manai', null, null, null, null, null, null),
            $this->r('Talud Depan Masjid Haqqul Yakin', 'RT.003/RW.002 Dusun Bonto Manai', 25, 'Meter', null, null, null, null),
            $this->r('Pembangunan Drainase', 'Dusun Bonto Manai', 100, 'Meter', null, null, null, null),
            $this->r('Rabat Beton', 'RT.001/RW.001 Dusun Katute', 100, 'Meter', null, null, null, null),
            $this->r('Pelebaran Jalan Padang Malabo', 'RT.001/RW.001 Dusun Katute', 800, 'Meter', null, null, null, null),
            $this->r('Duwikker', 'RT.001/RW.001 Dusun Katute', 2, 'Unit', null, null, null, null),
            $this->r('Rabat Beton', 'RT.002/RW.001 Dusun Katute', 200, 'Meter', null, null, null, null),
            $this->r('Talud Samping Masjid Darul Aftar', 'RT.002/RW.001 Dusun Katute', 40, 'Meter', null, null, null, null),
            $this->r('Talud PAUD', 'RT.003/RW.002 Dusun Katute', 50, 'Meter', null, null, null, null),
            $this->r('Pagar PAUD', 'Dusun Katute', null, null, null, null, null, null),
            $this->r('Rabat Beton Jalan Mattekko', 'Dusun Katute', 2000, 'Meter', null, null, null, null),
            $this->r('Duwikker', 'Dusun Katute', 2, 'Unit', null, null, null, null),
            $this->r('Renovasi Sekolah TK', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Stapel PAUD', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Rabat Beton', 'Dusun Katute/Dusun Balang', 40, 'Meter', null, null, null, null),
            $this->r('Tribun Lapangan', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Tambahan Insentif Guru PAUD', 'Desa Barambang', null, null, null, null, null, null),
            $this->r('Pembangunan Irigasi Air Terjun', 'Dusun Balang/Dusun Katute', 1800, 'Meter', null, null, null, null),
            $this->r('Rehabilitasi Hulu Air Bersih', 'Dusun Katute', 1000, 'Meter', null, null, null, null),
            $this->r('Rehabilitasi Pustu Barambang', 'Dusun Katute', 1, 'Unit', null, null, null, null),
            $this->r('Pengadaan Internet', 'Kantor Desa Barambang', 1, 'Paket', null, null, null, null),
            $this->r('Pembangunan Pintu Gerbang Kantor Desa', 'Kantor Desa Barambang', 1, 'Paket', null, null, null, null),
            $this->r('Pengadaan Bibit Kopi', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembuatan Empang/Budidaya Ikan Air Tawar', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembinaan TP PKK', 'Desa Barambang', 1, 'Desa', null, null, null, null),
            $this->r('Pembinaan Lembaga Adat', 'Desa Barambang', 1, 'Klp', null, null, null, null),
            $this->r('Pengadaan Prasarana Rumah Adat', 'Desa Barambang', 1, 'Ls', null, null, null, null),
            $this->r('Pengadaan Alat Musik Sanggar Seni', 'Desa Barambang', 1, 'Set', null, null, null, null),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items2026(): array
    {
        $fixedItems = [
            [1, 'Pembangunan Duwikker', 'Dusun Batu Massompo', 200000000],
            [2, 'Tambahan Sarana Air Bersih Pipa', 'Dusun Batu Massompo', 200000000],
            [3, 'Pengembangan Ternak Sapi', 'Dusun Batu Massompo', 200000000],
            [4, 'Pembangunan Lanjutan Wisata Batu Barae', 'Dusun Batu Massompo', 200000000],
            [5, 'Pelatihan Keagamaan', 'Dusun Bonto Manai', 2000000],
            [6, 'Pengadaan Bibit Pala', 'Dusun Batu Massompo', 5000000],
            [7, 'Pelatihan Kelompok Tani Pengaplikasian Pupuk dan Penanggulangan Hama Tanaman Padi', 'Dusun Bonto Manai', 4000000],
            [8, 'Rehabilitasi Gedung PAUD Bunda Apareng', 'Dusun Batu Massompo', 30000000],
            [9, 'Pembangunan Pagar PAUD Bunda Hapareng', 'Dusun Bonto Manai', 11000000],
            [10, 'Pengadaan APE Prosotan PAUD Bunda Apareng', 'Dusun Bonto Manai', 7000000],
            [11, 'Pembangunan Bendungan dan Saluran Irigasi', 'Dusun Bonto Manai', 150000000],
            [12, 'Rehabilitasi Masjid Haqqul Yakin', 'Dusun Bonto Manai', 10000000],
            [13, 'Pengadaan Sound System', 'Dusun Balang', 12000000],
            [14, 'Pembangunan Rabat Beton Jln. Poros Bonto Lasuna', 'Dusun Bonto Manai', 130000000],
            [15, 'Pelatihan Kerajinan Tangan Pemuda Tani Balang', 'Dusun Balang', 5000000],
            [16, 'Penambahan Insentif Koordinator dan Kader Posyandu', 'Dusun Balang', 200000000],
            [17, 'Pembangunan Jembatan Besi', 'Dusun Balang', 100000000],
            [18, 'Pembangunan Kantor LPM', 'Dusun Balang', 75000000],
            [19, 'Pelatihan Kerajinan Tangan Pemuda Tani', 'Dusun Balang', 5000000],
            [20, 'Penambahan/Rehabilitasi Sarana dan Prasarana Air Bersih/Pipa', 'Dusun Balang', 180000000],
            [21, 'Pengadaan Lampu Jalan', 'Dusun Batu Massompo', 8000000],
            [22, 'Pemeliharaan Jalan Poros Pasar', 'Dusun Katute', 62000000],
            [23, 'Pembangunan Talud Masjid Darul Aftar', 'Dusun Katute', 25000000],
            [24, 'Pengecoran Jln. Bintino', 'Dusun Katute', 130000000],
            [25, 'Pembangunan Pagar Sekolah PAUD', 'Dusun Katute', 35000000],
            [26, 'Pembangunan Irigasi Batu Barae', 'Dusun Katute', 70000000],
            [27, 'Pengadaan Lampu Jalan', 'Dusun Katute', 8000000],
            [28, 'Pengadaan Bak Sampah', 'Dusun Katute', 5000000],
            [29, 'Pengecoran Lanjutan Jln. Poros Pasar', 'Dusun Katute', 160000000],
            [30, 'Rehabilitasi Pagar Kantor Desa', 'Desa Barambang', 65000000],
            [31, 'Pengadaan Jaringan Internet', 'Desa Barambang', 5000000],
            [32, 'Penambahan Insentif RT/RW', 'Desa Barambang', 2000000],
            [33, 'Operasional LPM', 'Desa Barambang', 2000000],
            [34, 'Pengadaan Bak Sampah', 'Desa Barambang', 5000000],
            [35, 'Pengecoran Lanjutan Jln. Poros Pasar', 'Dusun Katute', 160000000],
            [36, 'Pengadaan Lampu Jalan', 'Dusun Katute', 8000000],
            [37, 'Pengadaan Alat Penanam Jagung', 'Desa Barambang', 1000000],
            [38, 'Pemberantasan Buta Aksara Al-Quran', 'Desa Barambang', 3000000],
            [39, 'Peningkatan SDM Kelompok Tani', 'Desa Barambang', 60000000],
            [40, 'Pengadaan/Pemanfaatan Limbah Kotoran Sapi Menjadi Biogas', 'Desa Barambang', 6000000],
            [41, 'Pembinaan Guru Mengaji', 'Desa Barambang', 2000000],
            [42, 'Pengadaan Perlengkapan Pakaian Adat', 'Desa Barambang', 4000000],
            [43, 'Pengadaan Induk Sapi Pasang Breeding', 'Desa Barambang', 45000000],
            [44, 'Biaya Kegiatan MTQ/STQ Tingkat Desa/Kecamatan/Kabupaten', 'Desa Barambang', 2000000],
            [45, 'Insentif Pengurus Adat', 'Desa Barambang', 160000000],
            [46, 'Pengadaan Perlengkapan Rumah Adat', 'Desa Barambang', 3500000],
            [47, 'Pengadaan Kendaraan Dinas Kepala Desa dan Perangkat Desa', 'Desa Barambang', 45000000],
            [48, 'Pelatihan Keterampilan/Kerajinan PKK Pokja 1, 2, 3, 4', 'Desa Barambang', 3000000],
            [49, 'Pemberdayaan Bidang Seni, Agama, Olahraga, Budaya dan Pendidikan', 'Desa Barambang', 4500000],
            [50, 'Pengadaan Bibit IB Sapi Pasang', 'Desa Barambang', 100350000],
            [51, 'Pengadaan Pencacah Rumput', 'Desa Barambang', 1500000],
            [52, 'Penyertaan Modal BUMDes', 'Desa Barambang', 200000000],
            [53, 'Pembinaan Majelis Taklim', 'Desa Barambang', 1500000],
            [54, 'Perlengkapan Sarana dan Prasarana Olahraga', 'Desa Barambang', 3000000],
            [55, 'Pelatihan Pembuatan Pestisida', 'Desa Barambang', 8000000],
            [56, 'Pembentukan Guru Mengaji Lanjutan', 'Desa Barambang', 2000000],
            [57, 'Pengadaan Timbangan Berat Badan Digital untuk Posyandu', 'Desa Barambang', 4000000],
            [58, 'Pembangunan Lanjutan Jembatan Liu Sirie', 'Dusun Bonto Manai', 100000000],
            [59, 'Rehabilitasi Posyandu', 'Dusun Katute', 25000000],
            [60, 'Pembangunan Los Pasar', 'Dusun Balang', 65000000],
            [61, 'Pengadaan Perlengkapan Sarana Jenazah', 'Desa Barambang', 5000000],
            [62, 'Pelatihan Pembuatan Pupuk Organik dan PGPR', 'Desa Barambang', 5000000],
            [63, 'Pengadaan Pakaian Seragam Koordinator dan Kader Posyandu', 'Desa Barambang', 4500000],
            [64, 'Pembangunan Talud Pasar', 'Dusun Katute', 11000000],
        ];

        // Pertahankan empat alternatif awal yang sudah digunakan pada alur
        // penilaian dan ELECTRE sebelumnya. Data lainnya tetap mengikuti PDF.
        $prioritasAlternatif = [
            'Pembangunan Talud Pasar' => 'RT.001/RW.001 Dusun Katute',
            'Pembangunan Los Pasar' => 'RT.001/RW.001 Dusun Katute',
            'Rehabilitasi Posyandu' => 'RT.002 Dusun Balang',
            'Pembangunan Lanjutan Jembatan Liu Sirie' => 'RT.001 Bonto Manai',
        ];

        $fixedItems = collect($fixedItems)
            ->sortBy(function (array $item) use ($prioritasAlternatif): int {
                $urutan = array_search($item[1], array_keys($prioritasAlternatif), true);

                return $urutan === false ? 4 + $item[0] : $urutan;
            })
            ->values()
            ->map(function (array $item, int $index) use ($prioritasAlternatif): array {
                $item[0] = $index + 1;
                $item[2] = $prioritasAlternatif[$item[1]] ?? $item[2];

                return $item;
            })
            ->all();

        $catalogue = collect($this->catalogue2026())->keyBy('nama');

        return array_map(function (array $fixed) use ($catalogue): array {
            [$nomor, $nama, $lokasi, $anggaran] = $fixed;
            $metadata = $catalogue->get($nama, $this->r($nama, $lokasi, null, null, null, null, null, null));

            return array_merge($metadata, compact('nomor', 'nama', 'lokasi', 'anggaran'));
        }, $fixedItems);
    }

    /**
     * Metadata volume, penerima manfaat, dan kategori dari dokumen perencanaan sebelumnya.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalogue2026(): array
    {
        return [
            $this->r('Pembangunan Talud Pasar', 'RT.001/RW.001 Dusun Katute', 50, 'Meter', 500, 400, 0, 'Infrastruktur/Talud'),
            $this->r('Pembangunan Los Pasar', 'RT.001/RW.001 Dusun Katute', 200, 'Meter', 500, 400, 0, 'Ekonomi/Infrastruktur'),
            $this->r('Rehabilitasi Posyandu', 'RT.002 Dusun Balang', 1, 'Unit', 248, 247, 0, 'Kesehatan'),
            $this->r('Pembangunan Lanjutan Jembatan Liu Sirie', 'RT.001 Bonto Manai', 13, 'Meter', 69, 71, 0, 'Infrastruktur Jembatan'),
            $this->r('Pengadaan Pakaian Seragam Koordinator dan Kader Posyandu', 'Desa Barambang', 23, 'Orang', 0, 23, 0, 'Kesehatan'),
            $this->r('Pengadaan Timbangan Berat Badan Digital untuk Posyandu', 'Desa Barambang', 4, 'Unit', 248, 247, 0, 'Kesehatan'),
            $this->r('Pelatihan Pembuatan Pupuk Organik dan PGPR', 'Desa Barambang', 8, 'Klpk', 248, 247, 0, 'Pertanian'),
            $this->r('Pengadaan Perlengkapan Sarana Jenazah', 'Desa Barambang', 4, 'Dusun', 453, 455, 0, 'Sosial'),
            $this->r('Pembentukan Guru Mengaji Lanjutan', 'Desa Barambang', 5, 'Orang', 2, 3, 0, 'Keagamaan'),
            $this->r('Pelatihan Pembuatan Pestisida', 'Desa Barambang', 8, 'Klpk', 248, 247, 0, 'Pertanian'),
            $this->r('Perlengkapan Sarana dan Prasarana Olahraga', 'Desa Barambang', 1, 'Ls', 248, 247, 0, 'Olahraga'),
            $this->r('Pembinaan Majelis Taklim', 'Desa Barambang', 4, 'Klp', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Penyertaan Modal BUMDes', 'Desa Barambang', 1, 'Unit', 1378, 1364, 0, 'Ekonomi'),
            $this->r('Pengadaan Pencacah Rumput', 'Desa Barambang', 6, 'Klp', 453, 455, 0, 'Pertanian'),
            $this->r('Pengadaan Bibit IB Sapi Pasang', 'Desa Barambang', 500, 'Ekor', 1378, 1364, 0, 'Peternakan'),
            $this->r('Pengadaan Kendaraan Dinas Kepala Desa dan Perangkat Desa', 'Desa Barambang', 12, 'Orang', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Pengadaan Perlengkapan Rumah Adat', 'Desa Barambang', 1, 'Unit', 1378, 1364, 0, 'Budaya'),
            $this->r('Pelatihan Keterampilan/Kerajinan PKK Pokja 1, 2, 3, 4', 'Desa Barambang', 4, 'Klp', 1378, 1364, 0, 'Pemberdayaan'),
            $this->r('Pemberdayaan Bidang Seni, Agama, Olahraga, Budaya dan Pendidikan', 'Desa Barambang', 5, 'Klp', 453, 455, 0, 'Pemberdayaan'),
            $this->r('Pengadaan Perlengkapan Pakaian Adat', 'Desa Barambang', 40, 'Orang', 1378, 1364, 0, 'Budaya'),
            $this->r('Insentif Pengurus Adat', 'Desa Barambang', 40, 'Orang', 1378, 1364, 0, 'Budaya'),
            $this->r('Biaya Kegiatan MTQ/STQ Tingkat Desa/Kecamatan/Kabupaten', 'Desa Barambang', 12, 'Kegiatan', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Pengadaan Induk Sapi Pasang Breeding', 'Desa Barambang', 6, 'Klp', 1378, 1364, 0, 'Peternakan'),
            $this->r('Pembinaan Guru Mengaji', 'Desa Barambang', 15, 'Orang', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Pengadaan/Pemanfaatan Limbah Kotoran Sapi Menjadi Biogas', 'Desa Barambang', 4, 'Klp', 453, 455, 0, 'Lingkungan'),
            $this->r('Peningkatan SDM Kelompok Tani', 'Desa Barambang', 6, 'Klp', 1378, 1364, 0, 'Pertanian'),
            $this->r('Pemberantasan Buta Aksara Al-Quran', 'Desa Barambang', 5, 'Unit', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Pengadaan Alat Penanam Jagung', 'Desa Barambang', 8, 'Klp', 453, 455, 0, 'Pertanian'),
            $this->r('Pengadaan Bak Sampah', 'Desa Barambang', 2, 'Unit', 1378, 1364, 0, 'Lingkungan'),
            $this->r('Operasional LPM', 'Barambang', 30, 'Orang', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Pengadaan Oven Pengering Cengkeh, Coklat dan Porang', 'Barambang', 4, 'Unit', 1378, 1364, 0, 'Ekonomi'),
            $this->r('Rehabilitasi Pagar Kantor Desa', 'Barambang', 40, 'Meter', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Pembangunan Pintu Gerbang Kantor Desa', 'Barambang', 1, 'Unit', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Program Tahfidz', 'Barambang', 1, 'Unit', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Pengadaan Jaringan Internet', 'Barambang', 1, 'Set', 1378, 1364, 0, 'Teknologi'),
            $this->r('Pengecoran Lanjutan Jln. Poros Pasar', 'RT.001/RW.001 Dusun Katute', 120, 'Meter', 500, 400, 0, 'Infrastruktur Jalan'),
            $this->r('Penambahan Insentif RT/RW', 'Barambang', 24, 'Orang', 24, 0, 0, 'Pemerintahan'),
            $this->r('Pengadaan Lampu Jalan', 'RT.001/RT.002/RT.003/RT.004 Dusun Katute', 4, 'Unit', 453, 455, 0, 'Infrastruktur'),
            $this->r('Pembangunan Pagar Sekolah PAUD', 'RT.003/RW.002 Dusun Katute', 160, 'Meter', 453, 455, 0, 'Pendidikan'),
            $this->r('Pengecoran Jln. Bintino', 'RT.002 Dusun Katute', 200, 'Meter', 500, 400, 0, 'Infrastruktur Jalan'),
            $this->r('Pembangunan Talud Masjid Darul Aftar', 'RT.002 Dusun Katute', 70, 'Meter', 453, 455, 0, 'Infrastruktur/Talud'),
            $this->r('Pembangunan Irigasi Batu Barae', 'Dusun Balang/Dusun Katute', 3000, 'Meter', 1378, 1364, 0, 'Irigasi'),
            $this->r('Pemeliharaan Jalan Poros Pasar', 'Dusun Katute', 2500, 'Meter', 1378, 1364, 0, 'Infrastruktur Jalan'),
            $this->r('Penambahan/Rehabilitasi Sarana dan Prasarana Air Bersih/Pipa', 'Dusun Katute', 3500, 'Meter', 453, 455, 0, 'Air Bersih'),
            $this->r('Pembangunan Kantor LPM', 'Katute', 1, 'Unit', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Pelatihan Kerajinan Tangan Pemuda Tani', 'Dusun Balang', 1, 'Klp', 15, 10, 0, 'Pemberdayaan'),
            $this->r('Pengadaan Lampu Jalan', 'Dusun Balang', 15, 'Buah', 248, 247, 0, 'Infrastruktur'),
            $this->r('Penambahan Insentif Koordinator dan Kader Posyandu', 'Dusun Balang', 8, 'Orang', 0, 8, 0, 'Kesehatan'),
            $this->r('Pembangunan Jembatan Besi', 'RT.001 Dusun Balang', 1400, 'Meter', 248, 247, 0, 'Infrastruktur Jembatan'),
            $this->r('Pelatihan Kerajinan Tangan Pemuda Tani Balang', 'Dusun Balang', 1, 'Klpk', 248, 247, 0, 'Pemberdayaan'),
            $this->r('Pengadaan Sound System', 'Dusun Balang', 1, 'Unit', 248, 247, 0, 'Sosial'),
            $this->r('Pembangunan Rabat Beton Jln. Poros Bonto Lasuna', 'RT.003/RT.004 Bonto Manai', 450, 'Meter', 259, 231, 0, 'Infrastruktur Jalan'),
            $this->r('Pembangunan Bendungan dan Saluran Irigasi', 'RT.003/RT.004 Bonto Manai', 1000, 'Meter', 100, 90, 0, 'Irigasi'),
            $this->r('Rehabilitasi Masjid Haqqul Yakin', 'RT.004 Bonto Manai', 1, 'Unit', 500, 400, 0, 'Keagamaan'),
            $this->r('Rehabilitasi Gedung PAUD Bunda Apareng', 'Bonto Manai', 1, 'Unit', 500, 400, 0, 'Pendidikan'),
            $this->r('Pengadaan APE Prosotan PAUD Bunda Apareng', 'Bonto Manai', 1, 'Unit', 500, 400, 0, 'Pendidikan'),
            $this->r('Pembangunan Pagar PAUD Bunda Hapareng', 'Bonto Manai', 13, 'Meter', 500, 400, 0, 'Pendidikan'),
            $this->r('Pelatihan Kelompok Tani Pengaplikasian Pupuk dan Penanggulangan Hama Tanaman Padi', 'Bonto Manai', 2, 'Klpk', 500, 400, 0, 'Pertanian'),
            $this->r('Pelatihan Keagamaan', 'Bonto Manai', 1, 'Klpk', 1378, 1364, 0, 'Keagamaan'),
            $this->r('Pengadaan Bibit Pala', 'Batu Massompo', 2000, 'Pohon', 48, 51, 0, 'Pertanian'),
            $this->r('Tambahan Sarana Air Bersih Pipa', 'RT.001/RT.002/RT.003/RT.004 Batu Massompo', 1500, 'Meter', 181, 186, 0, 'Air Bersih'),
            $this->r('Pengembangan Ternak Sapi', 'Batu Massompo', 2, 'Klp', 123, 0, 0, 'Peternakan'),
            $this->r('Pembangunan Lanjutan Wisata Batu Barae', 'Batu Massompo', 1, 'Unit', 1378, 1364, 0, 'Wisata'),
            $this->r('Pembangunan Duwikker', 'Batu Massompo', 3, 'Unit', 181, 186, 0, 'Infrastruktur'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function r(string $nama, string $lokasi, ?float $volume, ?string $satuan, ?int $lk, ?int $pr, ?int $artm, ?string $kategori): array
    {
        return compact('nama', 'lokasi', 'volume', 'satuan', 'lk', 'pr', 'artm', 'kategori');
    }

    /**
     * @param  Collection<string, Dusun>  $dusuns
     * @return array<int, int>
     */
    private function detectDusunIds(string $lokasi, $dusuns): array
    {
        $lokasi = strtolower($lokasi);
        $mapping = [
            'Dusun Katute' => ['dusun katute', 'katute'],
            'Dusun Balang' => ['dusun balang', 'balang'],
            'Dusun Bonto Manai' => ['dusun bonto manai', 'bonto manai'],
            'Dusun Batu Massompo' => ['dusun batu massompo', 'batu massompo'],
        ];

        $ids = [];

        foreach ($mapping as $namaDusun => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lokasi, $keyword)) {
                    $ids[] = $dusuns[$namaDusun]->id;
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}
