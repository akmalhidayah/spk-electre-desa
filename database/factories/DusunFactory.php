<?php

namespace Database\Factories;

use App\Models\Dusun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dusun>
 */
class DusunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_alternatif' => fake()->unique()->bothify('A##'),
            'nama_dusun' => 'Dusun '.fake()->unique()->city(),
            'luas_tanah' => fake()->randomFloat(2, 1, 500),
            'jumlah_penduduk' => fake()->numberBetween(100, 5000),
            'keterangan' => fake()->optional()->sentence(),
            'status' => Dusun::STATUS_AKTIF,
        ];
    }
}
