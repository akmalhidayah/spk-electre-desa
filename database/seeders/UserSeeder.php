<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Desa',
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'dusun_id' => null,
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'kepaladesa@example.com'],
            [
                'name' => 'Kepala Desa',
                'password' => $password,
                'role' => User::ROLE_KEPALA_DESA,
                'dusun_id' => null,
                'is_active' => true,
            ],
        );

        $kepalaDusuns = [
            'katute@example.com' => ['Kepala Dusun Katute', 'Dusun Katute'],
            'balang@example.com' => ['Kepala Dusun Balang', 'Dusun Balang'],
            'batumassompo@example.com' => ['Kepala Dusun Batu Massompo', 'Dusun Batu Massompo'],
            'bontomanai@example.com' => ['Kepala Dusun Bonto Manai', 'Dusun Bonto Manai'],
        ];

        foreach ($kepalaDusuns as $email => [$name, $namaDusun]) {
            $dusun = Dusun::where('nama_dusun', $namaDusun)->firstOrFail();

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'role' => User::ROLE_KEPALA_DUSUN,
                    'dusun_id' => $dusun->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
