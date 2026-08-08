<?php

namespace Database\Factories;

use App\Models\Dusun;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
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
            'tahun_perencanaan_id' => TahunPerencanaan::factory(),
            'dusun_id' => Dusun::factory(),
            'user_id' => User::factory(),
            'nama_kegiatan' => fake()->randomElement([
                'Pembangunan Jalan Dusun',
                'Perbaikan Drainase',
                'Pembangunan Talud',
                'Peningkatan Sarana Air Bersih',
            ]),
            'jumlah_usulan' => fake()->numberBetween(1, 10),
            'estimasi_anggaran' => fake()->randomFloat(2, 10000000, 250000000),
            'deskripsi' => fake()->optional()->paragraph(),
            'tipe_usulan' => UsulanPembangunan::TIPE_DUSUN,
            'status_usulan' => UsulanPembangunan::STATUS_DIAJUKAN,
            'status_pelaksanaan' => 'belum_dilaksanakan',
            'catatan_admin' => null,
        ];
    }
}
