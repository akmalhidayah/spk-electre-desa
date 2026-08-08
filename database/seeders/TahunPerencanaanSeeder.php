<?php

namespace Database\Seeders;

use App\Models\TahunPerencanaan;
use Illuminate\Database\Seeder;

class TahunPerencanaanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([2022, 2023, 2024, 2025, 2026] as $tahun) {
            TahunPerencanaan::updateOrCreate(
                ['tahun' => $tahun],
                [
                    'nama_periode' => "RKP Desa Barambang Tahun {$tahun}",
                    'deskripsi' => "Periode perencanaan Desa Barambang tahun {$tahun}.",
                    'is_active' => $tahun === 2026,
                    'is_locked' => false,
                    'perlu_hitung_ulang' => false,
                    'alasan_hitung_ulang' => null,
                ],
            );
        }
    }
}
