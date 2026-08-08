<?php

namespace Database\Factories;

use App\Models\TahunPerencanaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TahunPerencanaan> */
class TahunPerencanaanFactory extends Factory
{
    public function definition(): array
    {
        $tahun = fake()->unique()->numberBetween(2020, 2100);

        return [
            'tahun' => $tahun,
            'nama_periode' => "Periode {$tahun}",
            'is_active' => false,
            'is_locked' => false,
            'perlu_hitung_ulang' => false,
        ];
    }
}
