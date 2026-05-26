<?php

namespace Database\Factories;

use App\Models\Dusun;
use App\Models\UsulanPembangunan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsulanPembangunan>
 */
class UsulanPembangunanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dusun_id' => Dusun::factory(),
            'user_id' => User::factory(),
            'tahun' => now()->year,
            'nama_kegiatan' => fake()->randomElement([
                'Pembangunan Jalan Dusun',
                'Perbaikan Drainase',
                'Pembangunan Talud',
                'Peningkatan Sarana Air Bersih',
            ]),
            'jumlah_usulan' => fake()->numberBetween(1, 10),
            'estimasi_anggaran' => fake()->randomFloat(2, 10000000, 250000000),
            'deskripsi' => fake()->optional()->paragraph(),
            'status' => UsulanPembangunan::STATUS_DIAJUKAN,
            'catatan_admin' => null,
        ];
    }
}
