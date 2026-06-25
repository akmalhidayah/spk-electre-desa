<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TahunPerencanaanSeeder::class,
            DusunSeeder::class,
            KriteriaSeeder::class,
            UserSeeder::class,
            UsulanPembangunanSeeder::class,
            PenilaianAlternatifSeeder::class,
            WelcomeDesaSeeder::class,
        ]);
    }
}
