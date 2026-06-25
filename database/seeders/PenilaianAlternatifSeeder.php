<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenilaianAlternatifSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@example.com')->value('id');

        $values = [
            'Dusun Katute' => ['C1' => 4, 'C2' => 5, 'C3' => 5, 'C4' => 5, 'C5' => 4, 'C6' => 5],
            'Dusun Balang' => ['C1' => 3, 'C2' => 3, 'C3' => 3, 'C4' => 4, 'C5' => 3, 'C6' => 4],
            'Dusun Batu Massompo' => ['C1' => 3, 'C2' => 3, 'C3' => 3, 'C4' => 3, 'C5' => 3, 'C6' => 4],
            'Dusun Bonto Manai' => ['C1' => 4, 'C2' => 4, 'C3' => 4, 'C4' => 4, 'C5' => 4, 'C6' => 4],
        ];

        foreach ($values as $namaDusun => $criteriaValues) {
            $dusun = Dusun::where('nama_dusun', $namaDusun)->firstOrFail();

            foreach ($criteriaValues as $kodeKriteria => $nilai) {
                $kriteria = Kriteria::where('kode', $kodeKriteria)->firstOrFail();

                PenilaianAlternatif::updateOrCreate(
                    [
                        'tahun' => 2026,
                        'dusun_id' => $dusun->id,
                        'kriteria_id' => $kriteria->id,
                    ],
                    [
                        'nilai' => $nilai,
                        'keterangan' => 'Nilai awal berdasarkan rekap kebutuhan pembangunan RKP/RPJM Desa Barambang 2026.',
                        'created_by' => $adminId,
                    ],
                );
            }
        }
    }
}
