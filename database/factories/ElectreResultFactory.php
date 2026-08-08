<?php

namespace Database\Factories;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectreResult>
 */
class ElectreResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'electre_calculation_id' => ElectreCalculation::factory(),
            'usulan_pembangunan_id' => UsulanPembangunan::factory(),
            'kode_alternatif' => fake()->unique()->bothify('A##'),
            'nama_program_snapshot' => fake()->sentence(4),
            'lokasi_snapshot' => fake()->address(),
            'nama_dusun_snapshot' => fake()->optional()->city(),
            'ranking' => fake()->numberBetween(1, 10),
            'skor_dominasi' => fake()->numberBetween(0, 10),
            'total_preferensi' => fake()->randomFloat(8, 0, 1),
            'status_prioritas' => fake()->randomElement([
                'Prioritas Utama',
                'Prioritas Kedua',
                'Prioritas Ketiga',
            ]),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
