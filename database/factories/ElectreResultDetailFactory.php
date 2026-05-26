<?php

namespace Database\Factories;

use App\Models\ElectreCalculation;
use App\Models\ElectreResultDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectreResultDetail>
 */
class ElectreResultDetailFactory extends Factory
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
            'tahap' => fake()->randomElement(ElectreResultDetail::TAHAPS),
            'data' => [
                'rows' => [],
                'metadata' => [
                    'generated_by' => 'factory',
                ],
            ],
        ];
    }
}
