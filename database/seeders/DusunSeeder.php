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
                'nama_dusun' => 'Dusun Katute',
            ],
            [
                'nama_dusun' => 'Dusun Balang',
            ],
            [
                'nama_dusun' => 'Dusun Batu Massompo',
            ],
            [
                'nama_dusun' => 'Dusun Bonto Manai',
            ],
        ];

        foreach ($dusuns as $dusun) {
            Dusun::updateOrCreate(
                ['nama_dusun' => $dusun['nama_dusun']],
                [
                    'nama_dusun' => $dusun['nama_dusun'],
                    'status' => Dusun::STATUS_AKTIF,
                ],
            );
        }
    }
}
