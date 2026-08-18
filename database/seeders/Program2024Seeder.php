<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Program2024Seeder extends Seeder
{
    public function run(): void
    {
        $periode = TahunPerencanaan::firstOrCreate(
            ['tahun' => 2024],
            [
                'nama_periode' => 'RKP Desa Barambang Tahun 2024',
                'deskripsi' => 'Periode perencanaan Desa Barambang tahun 2024.',
                'is_active' => false,
                'is_locked' => false,
                'perlu_hitung_ulang' => false,
            ],
        );
        $adminId = User::where('email', 'admin@example.com')->value('id');
        $dusuns = Dusun::all()->keyBy('nama_dusun');

        DB::transaction(function () use ($periode, $adminId, $dusuns): void {
            foreach ($this->items() as $item) {
                $dusunIds = $this->detectDusunIds($item['lokasi'], $dusuns);
                $tipeUsulan = match (true) {
                    count($dusunIds) > 1 => UsulanPembangunan::TIPE_LINTAS_DUSUN,
                    count($dusunIds) === 1 => UsulanPembangunan::TIPE_DUSUN,
                    default => UsulanPembangunan::TIPE_UMUM_DESA,
                };

                $usulan = UsulanPembangunan::withTrashed()->firstOrNew([
                    'tahun_perencanaan_id' => $periode->id,
                    'sumber_dokumen' => 'Daftar Usulan Historis Desa Barambang Tahun 2024',
                    'nomor_dokumen' => (string) $item['nomor'],
                ]);

                $usulan->fill([
                    'dusun_id' => $tipeUsulan === UsulanPembangunan::TIPE_UMUM_DESA ? null : $dusunIds[0],
                    'user_id' => $adminId,
                    'nama_kegiatan' => $item['nama'],
                    'tipe_usulan' => $tipeUsulan,
                    'lokasi_kegiatan' => $item['lokasi'],
                    'prakiraan_volume' => $item['volume'],
                    'satuan' => $item['satuan'],
                    'penerima_manfaat_lk' => $item['lk'],
                    'penerima_manfaat_pr' => $item['pr'],
                    'penerima_manfaat_a_rtm' => $item['artm'],
                    'sdgs_ke' => null,
                    'sumber_usulan' => 'Dokumen usulan historis Desa Barambang Tahun 2024',
                    'sumber_dokumen' => 'Daftar Usulan Historis Desa Barambang Tahun 2024',
                    'nomor_dokumen' => (string) $item['nomor'],
                    'kategori_kegiatan' => null,
                    'jumlah_usulan' => 1,
                    'estimasi_anggaran' => null,
                    'anggaran_realisasi' => null,
                    'deskripsi' => null,
                    'status_usulan' => UsulanPembangunan::STATUS_DITERIMA,
                    'status_pelaksanaan' => 'belum_dilaksanakan',
                ]);
                $usulan->deleted_at = null;
                $usulan->save();
                $usulan->dusunsTerkait()->sync($dusunIds);
            }
        });

        $this->command?->info('Sebanyak 71 usulan historis Desa Barambang Tahun 2024 berhasil disimpan.');
    }

    /** @return array<int, array<string, mixed>> */
    private function items(): array
    {
        return [
            $this->row(1, 'Rhabilitasi Rabat Beton Jln. Barambang Manggappa', 'Rt.001/Rw.001 Dusun Katute', 100, 'Meter', 500, 400),
            $this->row(2, 'Rehabilitasi Rabat Beto Jln. Poros Pasar', 'Rt.001/Rw.001 Dusun Katute', 120, 'Meter', 500, 400),
            $this->row(3, 'Pembangunan Rabat Beton Lanjutan Jl. Sungai Bintino', 'Dusun Balang', 320, 'Meter', 1378, 1364),
            $this->row(4, 'Pembangunan Rabat Beton Jln Poros Merru Dg. Jarre', 'Rt. 002, Rw. 001 Bonto Manai', 150, 'Meter', 259, 231),
            $this->row(5, 'Pembangunan Rabat Beton Jln Poros Bonto Lasuna', 'Rt. 003, Rt. 004 Bonto Manai', 3, 'Meter', 259, 231),
            $this->row(6, 'Pembangnan Lanjutan Rabat Beton jln Batu Olengge', 'Batu Massompo', 200, 'Meter', 1378, 1364),
            $this->row(7, 'Pembangunan Lanjutan Los Pasar', 'Rt.001/Rw.001 Dusun Katute', 200, 'Meter', 500, 400),
            $this->row(8, 'Pembangunan Jembatan Besi', 'RT. 001 Dusun Balang', 1400, 'Meter', 248, 247),
            $this->row(9, 'Pembangunan Talud Jembatan Liu Sirie', 'Rt. 001 Bonto Manai', 13, 'Meter', 69, 71),
            $this->row(10, 'Pembangunan Bendungan dan Saluran Irigasi', 'Rt. 003, Rt. 004 Bonto Manai', 300, 'Meter', 100, 90),
            $this->row(11, 'Rehabilitasi Masjid Haqqul Yakin', 'Rt. 004 Bonto Manai', 1, 'Unit', 500, 400),
            $this->row(12, 'Rehabilitasi Posyandu', 'Bonto Manai', 1, 'Unit', 500, 400),
            $this->row(13, 'Rehabilitasi Posyandu', 'RT. 002 Dusun Balang', 1, 'Unit', 248, 247),
            $this->row(14, 'Pembangunan Paud', 'Bonto Manai', 1, 'Unit', 500, 400),
            $this->row(15, 'Pembangunan Pagar Paud Bunda Hapareng', 'Bonto Manai', 13, 'Meter', 500, 400),
            $this->row(16, 'Rehabilitasi Pagar Kantor Desa', 'Barambang', 40, 'Meter', 1378, 1364),
            $this->row(17, 'Pembangunan Lanjutan Wisata Batu Barae', 'Batu Massompo', 1, 'Unit', 1378, 1364),
            $this->row(18, 'Pembangunan Duwikker', 'Batu Massompo', 3, 'Unit', 181, 186),
            $this->row(19, 'Pengadaan Pengaman Posyandu', 'Dusun Balang', 1, 'Unit', 248, 247),
            $this->row(20, 'Pembangunan Skolah PAUD', 'Rt. 002 Bonto Manai', 1, 'Unit', 69, 71),
            $this->row(21, 'Pembangunan Pagar Sekolah PAUD', 'Rt.003/Rw.002 Dusun Katute', 160, 'Meter', 453, 455),
            $this->row(22, 'Pembangunan Talud Masjid Darul Aftar', 'Rt. 002 Dusun Katute', 70, 'Meter', 453, 455),
            $this->row(23, 'Pembangunan Irigasi batu Barae', 'Dusun Balang/Dusun Katute', 3000, 'Meter', 1378, 1364),
            $this->row(24, 'Pemeliharaan Jalan Poros Barambang Biji Nangka', 'Dusun Katute', 2500, 'Meter', 1378, 1364),
            $this->row(25, 'Pembangunan Kantor LPM', 'Katute', 1, 'Unit', 1378, 1364),
            $this->row(26, 'Pembangunan Talud Jembatan Liu Sirie', 'Rt. 001 Bonto Manai', 15, 'Meter', 69, 71),
            $this->row(27, 'Rehabilitasi Pagar Kantor Desa', 'Barambang', 40, 'Meter', 1378, 1364),
            $this->row(28, 'Pembangunan Pintu Gerbang Kantor Desa', 'Barambang', 1, 'Unit', 1378, 1364),
            $this->row(29, 'Pengadaan Pengaman Posyandu', 'Dusun Balang', 1, 'Unit', 248, 247),
            $this->row(30, 'Penambahan/Rehabilitasi Saranan dan Prasarana Air Bersih/Pipa', 'Dusun Katute', 3500, 'Meter', 453, 455),
            $this->row(31, 'Tambahan Sarana Air Bersih (Pipa)', 'Rt.001, Rt.002, Rt.003, Rt.004 Batu Massompo', 1500, 'Meter', 181, 186),
            $this->row(32, 'Pengadaan Pakaian Seragam Koordinator dan Kader Posyandu', 'Desa Barambang', 23, 'Orang', null, 23),
            $this->row(33, 'Pengadaan Timbangan Berat Badan Digital untuk Posyandu', 'Desa Barambang', 4, 'Unit', 248, 247),
            $this->row(34, 'Pelatihan Pebuatan Pupuk Organik dan PGPR', 'Desa Barambang', 8, 'Klpk', 248, 247),
            $this->row(35, 'Pengadaan Perlengkapan Sarana Jenazah', 'Desa Barambang', 4, 'Dusun', 453, 455),
            $this->row(36, 'Pembentukan Guru mengaji Lanjutan', 'Desa Barambang', 5, 'Orang', 2, 3),
            $this->row(37, 'Pelatihan Pembuatan Pestisida', 'Desa Barambang', 8, 'Klpk', 248, 247),
            $this->row(38, 'Perlengkapan Sarana dan Pra Sarana Olahraga', 'Desa Barambang', 1, 'Ls', 248, 247),
            $this->row(39, "Pembinaan Majelis Taq'lim", 'Desa Barambang', 4, 'Klp', 1378, 1364),
            $this->row(40, 'Penyertaan Modal Bumdes', 'Desa Barambang', 1, 'Unit', 1378, 1364),
            $this->row(41, 'Pengadaan Pencacah Rumput', 'Desa Barambang', 6, 'Klp', 453, 455),
            $this->row(42, 'Pengadaan Bibit IB (Sapi Pasang)', 'Desa Barambang', 500, 'Ekor', 1378, 1364),
            $this->row(43, 'Pengadaan Kendaraan Dinas Kepala Desa dan Perangkat Desa', 'Desa Barambang', 12, 'Orang', 1378, 1364),
            $this->row(44, 'Pengadaan Perlengkapan Rumah Adat', 'Desa Barambang', 1, 'Unit', 1378, 1364),
            $this->row(45, 'Pelatihan Keterampilan/Kerajinan PKK (Pokja 1,2,3,4)', 'Desa Barambang', 4, 'Klp', 1378, 1364),
            $this->row(46, 'Pemberdayaan Bidang Seni, Agama, Olahraga, Budaya dan Pendidikan', 'Desa Barambang', 5, 'Klp', 453, 455),
            $this->row(47, 'Pengadaan Perlengkapan Pakaian Adat', 'Desa Barambang', 40, 'Orang', 1378, 1364),
            $this->row(48, 'Insentif Pengurus Adat', 'Desa Barambang', 40, 'Orang', 1378, 1364),
            $this->row(49, 'Biaya Kegiatan MTQ/STQ Tingkat Desa/Kecamatan/Kabupaten', 'Desa Barambang', 12, 'Kegiatan', 1378, 1364),
            $this->row(50, 'Pengadaan Induk Sapi Pasang (Breending)', 'Desa Barambang', 6, 'Klp', 1378, 1364),
            $this->row(51, 'Pembinaan Guru mengaji', 'Desa Barambang', 15, 'Orang', 1378, 1364),
            $this->row(52, 'Pengadaan/Pemamfaatan Limbah Kotoran Sapi Menjadi Biogas', 'Desa Barambang', 4, 'Klp', 453, 455),
            $this->row(53, 'Peningkatan SDM Kelompok Tani', 'Desa Barambang', 6, 'Klp', 1378, 1364),
            $this->row(54, 'Pemberantasan Buta Aksara Al-Quran', 'Desa Barambang', 5, 'Unit', 1378, 1364),
            $this->row(55, 'Pengadaan Alat Penanam Jagung', 'Desa Barambang', 8, 'Klp', 453, 455),
            $this->row(56, 'Pengadaan Bak Sampah', 'Desa Barambang', 2, 'Unit', 1378, 1364),
            $this->row(57, 'Oprasional LPM', 'Barambang', 30, 'Orang', 1378, 1364),
            $this->row(58, 'Pengadaan Oven Pengering (Cengkeh, Coklat dan Porang)', 'Barambang', 4, 'Unit', 1378, 1364),
            $this->row(59, 'Program Tahfidz', 'Barambang', 1, 'Unit', 1378, 1364),
            $this->row(60, 'Pengadaan Jaringan Internet', 'Barambang', 1, 'Set', 1378, 1364),
            $this->row(61, 'Penambahan Insentif Rt/Rw', 'Barambang', 24, 'Orang', 24, null),
            $this->row(62, 'Pengadaan Lampu Jalan', 'Rt.001/Rt.002/Rt.003/Rt.004 Dusun Katute', 4, 'Unit', 453, 455),
            $this->row(63, 'Pelatihan Kerajinan Tangan Pemuda Tani', 'Dusun Balang', 1, 'Klp', 15, 10),
            $this->row(64, 'Pengadaan Lampu Jalan', 'Dusun Balang', 15, 'Buah', 248, 247),
            $this->row(65, 'Penambahan Insentif Koordinator dan Kader Posyandu', 'Dusun Balang', 8, 'Orang', null, 8),
            $this->row(66, 'Pelatihan Kerajinan Tangan Pemuda Tani Balang', 'Dusun Balang', 1, 'Klpk', 248, 247),
            $this->row(67, 'Pengadaan Shoun Sistem', 'Dusun Balang', 1, 'Unit', 248, 247),
            $this->row(68, 'Pelatihan Kelompok Tani (Pengaplikasian Pupuk dan Penaggulangan Hama Tanaman Padi', 'Desa Barambang', 2, 'Klpk', 500, 400),
            $this->row(69, 'Pelatiahn Keagamaan', 'Bonto Manai', 1, 'Klpk', 1378, 1364),
            $this->row(70, 'Pengadaan Bibit Pala', 'Desa Barambang', 2000, 'Pohong', 48, 51),
            $this->row(71, 'Pengembangan Ternak sapi', 'Batu Massompo', 2, 'Klp', 123, null),
        ];
    }

    /** @return array<string, mixed> */
    private function row(int $nomor, string $nama, string $lokasi, float $volume, string $satuan, ?int $lk, ?int $pr, ?int $artm = null): array
    {
        return compact('nomor', 'nama', 'lokasi', 'volume', 'satuan', 'lk', 'pr', 'artm');
    }

    /**
     * @param  Collection<string, Dusun>  $dusuns
     * @return array<int, int>
     */
    private function detectDusunIds(string $lokasi, Collection $dusuns): array
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
