<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use Illuminate\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kriterias = [
            [
                'kode' => 'C1',
                'nama_kriteria' => 'Cakupan Wilayah Program',
                'bobot' => 20,
                'urutan' => 1,
                'deskripsi' => 'Menilai luas cakupan wilayah dan kelompok yang dilayani program.',
                'skala_penilaian' => ['1' => 'Satu titik, kelompok, atau fasilitas', '2' => 'Satu RT', '3' => 'Beberapa RT dalam satu dusun', '4' => 'Seluruh atau sebagian besar satu dusun', '5' => 'Lebih dari satu dusun atau seluruh desa'],
            ],
            [
                'kode' => 'C2',
                'nama_kriteria' => 'Kelayakan Anggaran Program',
                'bobot' => 20,
                'urutan' => 2,
                'deskripsi' => 'Menilai kejelasan, efisiensi, dan keterjangkauan anggaran program.',
                'skala_penilaian' => ['1' => 'Anggaran belum jelas atau sangat sulit dibiayai', '2' => 'RAB tersedia tetapi anggaran sangat besar', '3' => 'Cukup layak tetapi perlu penyesuaian', '4' => 'Layak, RAB lengkap, dan memungkinkan dibiayai', '5' => 'Sangat layak, efisien, RAB lengkap, dan sumber dana tersedia'],
            ],
            [
                'kode' => 'C3',
                'nama_kriteria' => 'Penerima Manfaat Program',
                'bobot' => 20,
                'urutan' => 3,
                'deskripsi' => 'Menilai persentase masyarakat yang menerima manfaat program.',
                'skala_penilaian' => ['1' => 'Maksimal 10%', '2' => 'Lebih dari 10% sampai 25%', '3' => 'Lebih dari 25% sampai 50%', '4' => 'Lebih dari 50% sampai 75%', '5' => 'Lebih dari 75%'],
            ],
            [
                'kode' => 'C4',
                'nama_kriteria' => 'Kondisi Objek Pembangunan',
                'bobot' => 15,
                'urutan' => 4,
                'deskripsi' => 'Menilai kondisi aktual objek atau layanan yang menjadi sasaran pembangunan.',
                'skala_penilaian' => ['1' => 'Sangat baik atau kebutuhan hampir tidak ada', '2' => 'Baik atau kerusakan ringan', '3' => 'Cukup atau masalah sedang', '4' => 'Buruk, aktivitas atau pelayanan terganggu', '5' => 'Sangat buruk, rusak berat, tidak tersedia, atau membahayakan'],
            ],
            [
                'kode' => 'C5',
                'nama_kriteria' => 'Tingkat Urgensi Program',
                'bobot' => 15,
                'urutan' => 5,
                'deskripsi' => 'Menilai tingkat kemendesakan dan risiko bila program ditunda.',
                'skala_penilaian' => ['1' => 'Tidak mendesak', '2' => 'Kurang mendesak', '3' => 'Cukup mendesak', '4' => 'Mendesak dan berdampak besar', '5' => 'Sangat mendesak terkait keselamatan, layanan dasar, atau risiko serius'],
            ],
            [
                'kode' => 'C6',
                'nama_kriteria' => 'Kebutuhan Sarana dan Prasarana',
                'bobot' => 10,
                'urutan' => 6,
                'deskripsi' => 'Menilai tingkat kekurangan sarana dan prasarana pendukung.',
                'skala_penilaian' => ['1' => 'Sangat lengkap', '2' => 'Lengkap', '3' => 'Cukup tersedia', '4' => 'Kurang tersedia', '5' => 'Belum tersedia atau sangat kurang'],
            ],
        ];

        foreach ($kriterias as $kriteria) {
            Kriteria::updateOrCreate(
                ['kode' => $kriteria['kode']],
                [
                    'nama_kriteria' => $kriteria['nama_kriteria'],
                    'bobot' => $kriteria['bobot'],
                    'tipe' => Kriteria::TIPE_BENEFIT,
                    'deskripsi' => $kriteria['deskripsi'],
                    'skala_penilaian' => $kriteria['skala_penilaian'],
                    'urutan' => $kriteria['urutan'],
                    'status' => Kriteria::STATUS_AKTIF,
                ],
            );
        }
    }
}
