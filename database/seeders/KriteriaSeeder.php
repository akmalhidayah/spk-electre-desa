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
                'nama_kriteria' => 'Luas Tanah',
                'bobot' => 20,
                'urutan' => 1,
                'deskripsi' => 'Semakin besar luas tanah, semakin tinggi prioritas pembangunan.',
            ],
            [
                'kode' => 'C2',
                'nama_kriteria' => 'Daerah Pembangunan',
                'bobot' => 20,
                'urutan' => 2,
                'deskripsi' => 'Semakin strategis atau membutuhkan pembangunan, semakin tinggi prioritas.',
            ],
            [
                'kode' => 'C3',
                'nama_kriteria' => 'Kepadatan Penduduk',
                'bobot' => 20,
                'urutan' => 3,
                'deskripsi' => 'Semakin tinggi kepadatan penduduk terdampak, semakin tinggi prioritas.',
            ],
            [
                'kode' => 'C4',
                'nama_kriteria' => 'Kondisi Topografis',
                'bobot' => 15,
                'urutan' => 4,
                'deskripsi' => 'Semakin membutuhkan penanganan karena kondisi topografis, semakin tinggi prioritas.',
            ],
            [
                'kode' => 'C5',
                'nama_kriteria' => 'Potensi Ancaman Bencana',
                'bobot' => 15,
                'urutan' => 5,
                'deskripsi' => 'Semakin tinggi potensi ancaman bencana, semakin tinggi prioritas.',
            ],
            [
                'kode' => 'C6',
                'nama_kriteria' => 'Sarana dan Prasarana',
                'bobot' => 10,
                'urutan' => 6,
                'deskripsi' => 'Skala dibalik: nilai 5 berarti sarana dan prasarana sangat kurang lengkap sehingga lebih prioritas.',
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
                    'urutan' => $kriteria['urutan'],
                    'status' => Kriteria::STATUS_AKTIF,
                ],
            );
        }
    }
}
