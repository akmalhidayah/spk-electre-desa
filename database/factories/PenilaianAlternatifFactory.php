<?php

namespace Database\Factories;

use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
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
            'tahun_perencanaan_id' => TahunPerencanaan::factory(),
            'usulan_pembangunan_id' => UsulanPembangunan::factory(),
            'kriteria_id' => Kriteria::factory(),
            'nilai' => fake()->numberBetween(
                PenilaianAlternatif::NILAI_MIN,
                PenilaianAlternatif::NILAI_MAX,
            ),
            'keterangan' => fake()->optional()->sentence(),
            'sumber_data' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
