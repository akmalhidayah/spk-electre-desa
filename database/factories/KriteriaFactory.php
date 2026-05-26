<?php

namespace Database\Factories;

use App\Models\Kriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kriteria>
 */
class KriteriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->bothify('C##'),
            'nama_kriteria' => fake()->words(3, true),
            'bobot' => fake()->randomElement([10, 15, 20, 25]),
            'tipe' => Kriteria::TIPE_BENEFIT,
            'deskripsi' => fake()->optional()->sentence(),
            'urutan' => fake()->numberBetween(1, 20),
            'status' => Kriteria::STATUS_AKTIF,
        ];
    }
}
