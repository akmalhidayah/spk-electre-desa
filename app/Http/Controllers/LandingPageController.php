<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasiDesa;
use App\Models\WelcomeDesaSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $setting = $this->getActiveSetting();
        $struktur = $this->getActiveStruktur($setting);

        return view('landing.index', [
            'setting' => $setting,
            'struktur' => $struktur,
        ]);
    }

    private function getActiveSetting(): WelcomeDesaSetting
    {
        if ($this->hasTable('welcome_desa_settings')) {
            $setting = WelcomeDesaSetting::query()
                ->aktif()
                ->latest('id')
                ->first();

            if ($setting) {
                return $setting;
            }
        }

        return new WelcomeDesaSetting([
            'nama_desa' => 'Desa Barambang',
            'kecamatan' => 'Kecamatan Sinjai Borong',
            'kabupaten' => 'Kabupaten Sinjai',
            'provinsi' => 'Sulawesi Selatan',
            'alamat' => 'Desa Barambang, Kecamatan Sinjai Borong, Kabupaten Sinjai',
            'judul_welcome' => 'Selamat Datang di Website Resmi Desa Barambang',
            'deskripsi_welcome' => 'Sistem informasi desa dan pendukung keputusan prioritas pembangunan antar dusun.',
            'visi' => 'Terwujudnya desa yang maju, transparan, partisipatif, dan berbasis data dalam pembangunan.',
            'misi' => "Meningkatkan pelayanan publik desa.\nMendorong partisipasi masyarakat dalam pembangunan.\nMengelola data desa secara transparan dan akuntabel.",
            'judul_infografis' => 'Infografis Desa',
            'deskripsi_infografis' => 'Informasi wilayah dan peta desa.',
            'status_aktif' => true,
        ]);
    }

    /**
     * @return Collection<int, StrukturOrganisasiDesa>
     */
    private function getActiveStruktur(WelcomeDesaSetting $setting): Collection
    {
        if ($setting->exists && $this->hasTable('struktur_organisasi_desas')) {
            return $setting->strukturOrganisasi()
                ->aktif()
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get();
        }

        return collect([
            new StrukturOrganisasiDesa([
                'nama' => 'Nama Kepala Desa',
                'jabatan' => 'Kepala Desa',
                'deskripsi' => 'Pimpinan pemerintah desa.',
                'urutan' => 1,
                'status_aktif' => true,
            ]),
            new StrukturOrganisasiDesa([
                'nama' => 'Nama Sekretaris Desa',
                'jabatan' => 'Sekretaris Desa',
                'deskripsi' => 'Koordinator administrasi pemerintahan desa.',
                'urutan' => 2,
                'status_aktif' => true,
            ]),
            new StrukturOrganisasiDesa([
                'nama' => 'Nama Kepala Dusun',
                'jabatan' => 'Kepala Dusun',
                'deskripsi' => 'Pelaksana pelayanan wilayah dusun.',
                'urutan' => 3,
                'status_aktif' => true,
            ]),
        ]);
    }

    private function hasTable(string $table): bool
    {
        if (! $this->databaseLooksReachable()) {
            return false;
        }

        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function databaseLooksReachable(): bool
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            return true;
        }

        $config = config('database.connections.mysql');

        if (! empty($config['unix_socket'])) {
            return true;
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? 3306);
        $errno = 0;
        $errstr = '';

        $socket = @fsockopen($host, $port, $errno, $errstr, 0.35);

        if (! $socket) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
