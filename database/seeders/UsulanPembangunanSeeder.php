<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;

class UsulanPembangunanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $tahun = 2026;

        $items = [
            ['Dusun Katute', 'Pembangunan Jalan Dusun', 1, 175000000, UsulanPembangunan::STATUS_DIPROSES],
            ['Dusun Balang', 'Perbaikan Drainase Permukiman', 2, 95000000, UsulanPembangunan::STATUS_DIAJUKAN],
            ['Dusun Batu Massompo', 'Pembangunan Talud Penahan Tanah', 1, 145000000, UsulanPembangunan::STATUS_DITERIMA],
            ['Dusun Bonto Manai', 'Peningkatan Sarana Air Bersih', 4, 225000000, UsulanPembangunan::STATUS_MASUK_PRIORITAS],
            ['Dusun Katute', 'Rehabilitasi Jembatan Kecil', 1, 120000000, UsulanPembangunan::STATUS_DIAJUKAN],
            ['Dusun Balang', 'Pengadaan Lampu Jalan Dusun', 12, 60000000, UsulanPembangunan::STATUS_DIPROSES],
            ['Dusun Batu Massompo', 'Pengerasan Jalan Tani', 1, 185000000, UsulanPembangunan::STATUS_DITERIMA],
            ['Dusun Bonto Manai', 'Renovasi Posyandu Dusun', 1, 75000000, UsulanPembangunan::STATUS_DIAJUKAN],
            ['Dusun Katute', 'Pembangunan Saluran Irigasi', 1, 210000000, UsulanPembangunan::STATUS_MASUK_PRIORITAS],
            ['Dusun Balang', 'Perbaikan Gorong-gorong', 3, 85000000, UsulanPembangunan::STATUS_DITOLAK],
        ];

        foreach ($items as [$namaDusun, $namaKegiatan, $jumlah, $anggaran, $status]) {
            $dusun = Dusun::where('nama_dusun', $namaDusun)->firstOrFail();
            $pengaju = User::where('dusun_id', $dusun->id)->first() ?? $admin;

            UsulanPembangunan::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'dusun_id' => $dusun->id,
                    'nama_kegiatan' => $namaKegiatan,
                ],
                [
                    'user_id' => $pengaju?->id,
                    'jumlah_usulan' => $jumlah,
                    'estimasi_anggaran' => $anggaran,
                    'deskripsi' => "Usulan {$namaKegiatan} untuk mendukung kebutuhan pembangunan {$namaDusun}.",
                    'status' => $status,
                    'catatan_admin' => $status === UsulanPembangunan::STATUS_DITOLAK
                        ? 'Perlu kelengkapan data teknis sebelum diajukan kembali.'
                        : null,
                ],
            );
        }
    }
}
