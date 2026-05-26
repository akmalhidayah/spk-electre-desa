<?php

namespace Database\Factories;

use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
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
            'dusun_id' => Dusun::factory(),
            'ranking' => fake()->numberBetween(1, 10),
            'skor_dominasi' => fake()->numberBetween(0, 10),
            'status_prioritas' => fake()->randomElement([
                'Prioritas Utama',
                'Prioritas Kedua',
                'Prioritas Ketiga',
            ]),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
