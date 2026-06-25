<?php

namespace Database\Seeders;

use App\Models\TahunPerencanaan;
use Illuminate\Database\Seeder;

class TahunPerencanaanSeeder extends Seeder
{
    public function run(): void
    {
        TahunPerencanaan::updateOrCreate(
            ['tahun' => 2026],
            [
                'nama_periode' => 'RKP Desa Barambang Tahun 2026',
                'deskripsi' => 'Periode perencanaan usulan RKP/RPJM Desa Barambang tahun 2026.',
                'is_active' => true,
                'is_locked' => false,
                'perlu_hitung_ulang' => false,
                'alasan_hitung_ulang' => null,
            ],
        );
    }
}
