<?php

namespace Database\Seeders;

use App\Models\Dusun;
use Illuminate\Database\Seeder;

class DusunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dusuns = [
            [
                'kode_alternatif' => 'A1',
                'nama_dusun' => 'Dusun Katute',
            ],
            [
                'kode_alternatif' => 'A2',
                'nama_dusun' => 'Dusun Balang',
            ],
            [
                'kode_alternatif' => 'A3',
                'nama_dusun' => 'Dusun Batu Massompo',
            ],
            [
                'kode_alternatif' => 'A4',
                'nama_dusun' => 'Dusun Bonto Manai',
            ],
        ];

        foreach ($dusuns as $dusun) {
            Dusun::updateOrCreate(
                ['kode_alternatif' => $dusun['kode_alternatif']],
                [
                    'nama_dusun' => $dusun['nama_dusun'],
                    'status' => Dusun::STATUS_AKTIF,
                ],
            );
        }
    }
}
