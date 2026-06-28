<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UsulanPembangunanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $dusuns = Dusun::all()->keyBy('nama_dusun');

        foreach ($this->items() as $item) {
            $relatedDusunIds = $this->detectDusunIds($item['lokasi'], $dusuns);
            $tipeUsulan = match (true) {
                count($relatedDusunIds) > 1 => UsulanPembangunan::TIPE_LINTAS_DUSUN,
                count($relatedDusunIds) === 1 => UsulanPembangunan::TIPE_DUSUN,
                default => UsulanPembangunan::TIPE_UMUM_DESA,
            };

            $usulan = UsulanPembangunan::updateOrCreate(
                [
                    'tahun' => 2026,
                    'nama_kegiatan' => $item['nama'],
                    'lokasi_kegiatan' => $item['lokasi'],
                ],
                [
                    'dusun_id' => $tipeUsulan === UsulanPembangunan::TIPE_UMUM_DESA ? null : $relatedDusunIds[0],
                    'user_id' => $admin?->id,
                    'tipe_usulan' => $tipeUsulan,
                    'prakiraan_volume' => $item['volume'],
                    'satuan' => $item['satuan'],
                    'penerima_manfaat_lk' => $item['lk'],
                    'penerima_manfaat_pr' => $item['pr'],
                    'penerima_manfaat_a_rtm' => $item['artm'],
                    'sdgs_ke' => null,
                    'sumber_usulan' => 'RKP/RPJM Desa Barambang',
                    'kategori_kegiatan' => $item['kategori'],
                    'jumlah_usulan' => 1,
                    'estimasi_anggaran' => null,
                    'deskripsi' => "Usulan {$item['nama']} pada {$item['lokasi']}.",
                    'status' => UsulanPembangunan::STATUS_DITERIMA,
                    'status_prioritas' => UsulanPembangunan::PRIORITAS_NON_PRIORITAS,
                    'is_data_pendukung_penilaian' => $tipeUsulan !== UsulanPembangunan::TIPE_UMUM_DESA,
                ],
            );

            $usulan->dusunsTerkait()->sync($relatedDusunIds);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items(): array
    {
        return [
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
            $this->r('Pembangunan Talud Pasar', 'RT.001/RW.001 Dusun Katute', 50, 'Meter', 500, 400, 0, 'Infrastruktur/Talud'),
            $this->r('Penambahan Insentif RT/RW', 'Barambang', 24, 'Orang', 24, 0, 0, 'Pemerintahan'),
            $this->r('Pembangunan Los Pasar', 'RT.001/RW.001 Dusun Katute', 200, 'Meter', 500, 400, 0, 'Ekonomi/Infrastruktur'),
            $this->r('Pengadaan Lampu Jalan', 'RT.001/RT.002/RT.003/RT.004 Dusun Katute', 4, 'Unit', 453, 455, 0, 'Infrastruktur'),
            $this->r('Pembangunan Pagar Sekolah PAUD', 'RT.003/RW.002 Dusun Katute', 160, 'Meter', 453, 455, 0, 'Pendidikan'),
            $this->r('Pengecoran Jln. Bintino', 'RT.002 Dusun Katute', 200, 'Meter', 500, 400, 0, 'Infrastruktur Jalan'),
            $this->r('Pembangunan Talud Masjid Darul Aftar', 'RT.002 Dusun Katute', 70, 'Meter', 453, 455, 0, 'Infrastruktur/Talud'),
            $this->r('Pembangunan Irigasi Batu Barae', 'Dusun Balang/Dusun Katute', 3000, 'Meter', 1378, 1364, 0, 'Irigasi'),
            $this->r('Pemeliharaan Jalan Poros Pasar', 'Dusun Katute', 2500, 'Meter', 1378, 1364, 0, 'Infrastruktur Jalan'),
            $this->r('Penambahan/Rehabilitasi Sarana dan Prasarana Air Bersih/Pipa', 'Dusun Katute', 3500, 'Meter', 453, 455, 0, 'Air Bersih'),
            $this->r('Pembangunan Kantor LPM', 'Katute', 1, 'Unit', 1378, 1364, 0, 'Pemerintahan'),
            $this->r('Pelatihan Kerajinan Tangan Pemuda Tani', 'Dusun Balang', 1, 'Klp', 15, 10, 0, 'Pemberdayaan'),
            $this->r('Rehabilitasi Posyandu', 'RT.002 Dusun Balang', 1, 'Unit', 248, 247, 0, 'Kesehatan'),
            $this->r('Pengadaan Lampu Jalan', 'Dusun Balang', 15, 'Buah', 248, 247, 0, 'Infrastruktur'),
            $this->r('Penambahan Insentif Koordinator dan Kader Posyandu', 'Dusun Balang', 8, 'Orang', 0, 8, 0, 'Kesehatan'),
            $this->r('Pembangunan Jembatan Besi', 'RT.001 Dusun Balang', 1400, 'Meter', 248, 247, 0, 'Infrastruktur Jembatan'),
            $this->r('Pelatihan Kerajinan Tangan Pemuda Tani Balang', 'Dusun Balang', 1, 'Klpk', 248, 247, 0, 'Pemberdayaan'),
            $this->r('Pengadaan Sound System', 'Dusun Balang', 1, 'Unit', 248, 247, 0, 'Sosial'),
            $this->r('Pembangunan Lanjutan Jembatan Liu Sirie', 'RT.001 Bonto Manai', 13, 'Meter', 69, 71, 0, 'Infrastruktur Jembatan'),
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
    private function r(string $nama, string $lokasi, float $volume, string $satuan, int $lk, int $pr, int $artm, string $kategori): array
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
