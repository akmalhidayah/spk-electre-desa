<?php

namespace Database\Factories;

use App\Models\ElectreCalculation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectreCalculation>
 */
class ElectreCalculationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tahun = now()->year;

        return [
            'kode_perhitungan' => 'EL-'.$tahun.'-'.fake()->unique()->numerify('####'),
            'tahun' => $tahun,
            'judul' => 'Perhitungan ELECTRE Tahun '.$tahun,
            'deskripsi' => fake()->optional()->sentence(),
            'status' => ElectreCalculation::STATUS_DRAFT,
            'total_alternatif' => 0,
            'total_kriteria' => 0,
            'calculated_by' => User::factory(),
            'calculated_at' => null,
            'notes' => null,
        ];
    }
}
