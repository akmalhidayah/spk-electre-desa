<?php

namespace Database\Seeders;

use App\Models\StrukturOrganisasiDesa;
use App\Models\WelcomeDesaSetting;
use Illuminate\Database\Seeder;

class WelcomeDesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = WelcomeDesaSetting::query()->firstOrCreate(
            ['nama_desa' => 'Desa Barambang'],
            [
                'kecamatan' => 'Kecamatan Sinjai Borong',
                'kabupaten' => 'Kabupaten Sinjai',
                'provinsi' => 'Sulawesi Selatan',
                'judul_welcome' => 'Selamat Datang di Website Resmi Desa Barambang',
                'deskripsi_welcome' => 'Sistem informasi desa dan pendukung keputusan prioritas pembangunan antar dusun.',
                'visi' => 'Terwujudnya desa yang maju, transparan, partisipatif, dan berbasis data dalam pembangunan.',
                'misi' => "Meningkatkan kualitas pelayanan publik desa.\nMendorong keterlibatan masyarakat dalam perencanaan pembangunan.\nMenguatkan pengelolaan data desa yang transparan dan akuntabel.",
                'judul_infografis' => 'Infografis Desa',
                'deskripsi_infografis' => 'Informasi wilayah dan peta desa.',
                'status_aktif' => true,
            ]
        );

        $items = [
            ['nama' => 'Nama Kepala Desa', 'jabatan' => 'Kepala Desa', 'urutan' => 1],
            ['nama' => 'Nama Sekretaris Desa', 'jabatan' => 'Sekretaris Desa', 'urutan' => 2],
            ['nama' => 'Nama Kepala Dusun', 'jabatan' => 'Kepala Dusun', 'urutan' => 3],
        ];

        foreach ($items as $item) {
            StrukturOrganisasiDesa::query()->firstOrCreate(
                [
                    'welcome_desa_setting_id' => $setting->id,
                    'jabatan' => $item['jabatan'],
                ],
                [
                    'nama' => $item['nama'],
                    'urutan' => $item['urutan'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
