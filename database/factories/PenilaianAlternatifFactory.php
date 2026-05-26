<?php

namespace Database\Factories;

use App\Models\Dusun;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenilaianAlternatif>
 */
class PenilaianAlternatifFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tahun' => now()->year,
            'dusun_id' => Dusun::factory(),
            'kriteria_id' => Kriteria::factory(),
            'nilai' => fake()->numberBetween(
                PenilaianAlternatif::NILAI_MIN,
                PenilaianAlternatif::NILAI_MAX,
            ),
            'keterangan' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
