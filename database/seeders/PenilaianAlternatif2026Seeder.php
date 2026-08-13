<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;
use RuntimeException;

class PenilaianAlternatif2026Seeder extends Seeder
{
    public function run(): void
    {
        $periode = TahunPerencanaan::where('tahun', 2026)->firstOrFail();
        $adminId = User::where('email', 'admin@example.com')->value('id');
        $kriterias = Kriteria::whereIn('kode', ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])
            ->get()
            ->keyBy('kode');
        $programs = UsulanPembangunan::periode($periode->id)
            ->diterima()
            ->orderBy('nomor_dokumen')
            ->get();

        if ($programs->count() !== 64) {
            throw new RuntimeException('Seeder penilaian 2026 membutuhkan tepat 64 program PDF. Jalankan UsulanPembangunanSeeder terlebih dahulu.');
        }

        if ($kriterias->count() !== 6) {
            throw new RuntimeException('Kriteria C1 sampai C6 belum lengkap. Jalankan KriteriaSeeder terlebih dahulu.');
        }

        $nilaiPerProgram = $this->nilaiPerProgram();
        $nilaiNetral = [3, 3, 3, 3, 3, 3];

        foreach ($programs as $program) {
            $nilai = $this->resolveNilai($program, $nilaiPerProgram);
            $menggunakanNilaiNetral = $nilai === null;
            $nilai ??= $nilaiNetral;

            foreach (['C1', 'C2', 'C3', 'C4', 'C5', 'C6'] as $index => $kodeKriteria) {
                PenilaianAlternatif::updateOrCreate(
                    [
                        'tahun_perencanaan_id' => $periode->id,
                        'usulan_pembangunan_id' => $program->id,
                        'kriteria_id' => $kriterias[$kodeKriteria]->id,
                    ],
                    [
                        'nilai' => $nilai[$index],
                        'keterangan' => $menggunakanNilaiNetral
                            ? 'Nilai netral untuk program PDF yang belum memiliki nilai sumber.'
                            : null,
                        'sumber_data' => 'Data penilaian program pembangunan 2026',
                        'created_by' => $adminId,
                    ],
                );
            }
        }

        $this->command?->info('Penilaian 64 program Tahun 2026 berhasil disimpan (384 nilai C1-C6).');
    }

    /**
     * @param  array<string, array<int, int>>  $nilaiPerProgram
     * @return array<int, int>|null
     */
    private function resolveNilai(UsulanPembangunan $program, array $nilaiPerProgram): ?array
    {
        if ($program->nama_kegiatan === 'Pengadaan Lampu Jalan') {
            return match (true) {
                str_contains($program->lokasi_kegiatan, 'Balang') => [4, 4, 4, 4, 4, 4],
                str_contains($program->lokasi_kegiatan, 'Katute') => [4, 3, 3, 4, 3, 4],
                default => null,
            };
        }

        if ($program->nama_kegiatan === 'Pengadaan Bak Sampah'
            && $program->lokasi_kegiatan !== 'Desa Barambang') {
            return null;
        }

        return $nilaiPerProgram[$program->nama_kegiatan] ?? null;
    }

    /**
     * Nilai sumber dicocokkan berdasarkan nama resmi program, bukan nomor urut
     * PDF. Dengan demikian program duplikat pada PDF memperoleh nilai yang sama.
     *
     * @return array<string, array<int, int>>
     */
    private function nilaiPerProgram(): array
    {
        $namaProgram = [
            'Pembangunan Talud Pasar',
            'Pembangunan Los Pasar',
            'Rehabilitasi Posyandu',
            'Pembangunan Lanjutan Jembatan Liu Sirie',
            'Pengadaan Pakaian Seragam Koordinator dan Kader Posyandu',
            'Pengadaan Timbangan Berat Badan Digital untuk Posyandu',
            'Pelatihan Pembuatan Pupuk Organik dan PGPR',
            'Pengadaan Perlengkapan Sarana Jenazah',
            'Pembentukan Guru Mengaji Lanjutan',
            'Pelatihan Pembuatan Pestisida',
            'Perlengkapan Sarana dan Prasarana Olahraga',
            'Pembinaan Majelis Taklim',
            'Penyertaan Modal BUMDes',
            'Pengadaan Pencacah Rumput',
            'Pengadaan Bibit IB Sapi Pasang',
            'Pengadaan Kendaraan Dinas Kepala Desa dan Perangkat Desa',
            'Pengadaan Perlengkapan Rumah Adat',
            'Pelatihan Keterampilan/Kerajinan PKK Pokja 1, 2, 3, 4',
            'Pemberdayaan Bidang Seni, Agama, Olahraga, Budaya dan Pendidikan',
            'Pengadaan Perlengkapan Pakaian Adat',
            'Insentif Pengurus Adat',
            'Biaya Kegiatan MTQ/STQ Tingkat Desa/Kecamatan/Kabupaten',
            'Pengadaan Induk Sapi Pasang Breeding',
            'Pembinaan Guru Mengaji',
            'Pengadaan/Pemanfaatan Limbah Kotoran Sapi Menjadi Biogas',
            'Peningkatan SDM Kelompok Tani',
            'Pemberantasan Buta Aksara Al-Quran',
            'Pengadaan Alat Penanam Jagung',
            'Pengadaan Bak Sampah',
            'Operasional LPM',
            'Pengadaan Oven Pengering Cengkeh, Coklat dan Porang',
            'Rehabilitasi Pagar Kantor Desa',
            'Pembangunan Pintu Gerbang Kantor Desa',
            'Program Tahfidz',
            'Pengadaan Jaringan Internet',
            'Pengecoran Lanjutan Jln. Poros Pasar',
            'Penambahan Insentif RT/RW',
            'Pengadaan Lampu Jalan',
            'Pembangunan Pagar Sekolah PAUD',
            'Pengecoran Jln. Bintino',
            'Pembangunan Talud Masjid Darul Aftar',
            'Pembangunan Irigasi Batu Barae',
            'Pemeliharaan Jalan Poros Pasar',
            'Penambahan/Rehabilitasi Sarana dan Prasarana Air Bersih/Pipa',
            'Pembangunan Kantor LPM',
            'Pelatihan Kerajinan Tangan Pemuda Tani',
            'Pengadaan Lampu Jalan',
            'Penambahan Insentif Koordinator dan Kader Posyandu',
            'Pembangunan Jembatan Besi',
            'Pelatihan Kerajinan Tangan Pemuda Tani Balang',
            'Pengadaan Sound System',
            'Pembangunan Rabat Beton Jln. Poros Bonto Lasuna',
            'Pembangunan Bendungan dan Saluran Irigasi',
            'Rehabilitasi Masjid Haqqul Yakin',
            'Rehabilitasi Gedung PAUD Bunda Apareng',
            'Pengadaan APE Prosotan PAUD Bunda Apareng',
            'Pembangunan Pagar PAUD Bunda Hapareng',
            'Pelatihan Kelompok Tani Pengaplikasian Pupuk dan Penanggulangan Hama Tanaman Padi',
            'Pelatihan Keagamaan',
            'Pengadaan Bibit Pala',
            'Tambahan Sarana Air Bersih Pipa',
            'Pengembangan Ternak Sapi',
            'Pembangunan Lanjutan Wisata Batu Barae',
            'Pembangunan Duwikker',
        ];

        $nilai = [
            [4, 5, 5, 5, 4, 5], [4, 3, 3, 4, 3, 4], [4, 3, 3, 3, 3, 4], [4, 4, 4, 4, 4, 4],
            [3, 2, 3, 3, 3, 4], [3, 3, 4, 3, 3, 4], [2, 3, 3, 3, 4, 4], [4, 3, 2, 2, 3, 4],
            [4, 3, 4, 3, 2, 4], [2, 3, 2, 4, 3, 2], [4, 3, 2, 3, 4, 3], [3, 3, 2, 3, 4, 3],
            [3, 4, 3, 2, 2, 4], [3, 2, 2, 2, 2, 3], [3, 3, 2, 3, 3, 4], [4, 3, 3, 4, 4, 4],
            [2, 3, 4, 3, 3, 4], [3, 4, 3, 3, 4, 4], [3, 2, 2, 3, 3, 3], [2, 2, 2, 3, 3, 4],
            [3, 2, 2, 3, 3, 3], [3, 2, 2, 2, 3, 3], [3, 2, 2, 2, 3, 2], [2, 2, 3, 3, 2, 3],
            [4, 4, 4, 4, 4, 4], [3, 3, 2, 4, 3, 4], [3, 2, 3, 2, 4, 4], [4, 3, 2, 4, 3, 4],
            [3, 3, 3, 2, 3, 3], [4, 3, 4, 3, 2, 2], [3, 3, 3, 2, 3, 3], [3, 2, 3, 3, 2, 3],
            [2, 3, 4, 3, 2, 3], [4, 3, 2, 3, 3, 3], [3, 2, 2, 3, 3, 4], [3, 2, 3, 4, 3, 3],
            [3, 3, 3, 3, 2, 3], [4, 3, 3, 4, 3, 4], [4, 3, 4, 3, 3, 4], [3, 3, 3, 4, 3, 3],
            [3, 3, 3, 3, 5, 4], [4, 3, 3, 3, 4, 4], [2, 3, 3, 3, 3, 4], [3, 3, 3, 3, 3, 4],
            [4, 4, 4, 4, 4, 4], [4, 4, 4, 4, 4, 4], [4, 4, 4, 4, 4, 4], [3, 2, 3, 4, 3, 4],
            [4, 3, 5, 3, 3, 3], [2, 2, 3, 4, 3, 3], [3, 3, 3, 2, 1, 3], [4, 4, 5, 4, 4, 4],
            [3, 4, 3, 3, 4, 4], [3, 3, 3, 3, 3, 3], [3, 2, 3, 3, 4, 3], [4, 3, 3, 3, 2, 3],
            [4, 4, 3, 3, 2, 3], [3, 3, 4, 2, 3, 4], [4, 4, 4, 4, 4, 4], [4, 3, 3, 3, 2, 3],
            [4, 4, 4, 4, 4, 4], [4, 4, 3, 3, 3, 4], [4, 2, 3, 3, 3, 4], [4, 3, 4, 4, 3, 5],
        ];

        return array_combine($namaProgram, $nilai);
    }
}
