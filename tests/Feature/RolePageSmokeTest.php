<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\Kriteria;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_halaman_html_utama_dapat_dibuka_sesuai_peran(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $kepalaDesa = User::where('email', 'kepaladesa@example.com')->firstOrFail();
        $kepalaDusun = User::where('email', 'katute@example.com')->firstOrFail();
        $dusun = Dusun::firstOrFail();
        $kriteria = Kriteria::firstOrFail();
        $periode = TahunPerencanaan::firstOrFail();
        $user = User::whereKeyNot($admin->id)->firstOrFail();
        $usulan = UsulanPembangunan::firstOrFail();

        $adminPages = [
            route('admin.dashboard'),
            route('admin.dusuns.index'),
            route('admin.dusuns.create'),
            route('admin.dusuns.edit', $dusun),
            route('admin.kriterias.index'),
            route('admin.kriterias.create'),
            route('admin.kriterias.edit', $kriteria),
            route('admin.tahun-perencanaan.index'),
            route('admin.tahun-perencanaan.create'),
            route('admin.tahun-perencanaan.edit', $periode),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', $user),
            route('admin.users.reset-password', $user),
            route('admin.usulan.index'),
            route('admin.usulan.create'),
            route('admin.usulan.edit', $usulan),
            route('admin.penilaian.index', ['tahun' => 2026]),
            route('admin.electre.index', ['tahun' => 2026]),
            route('admin.hasil-rekomendasi.index', ['tahun' => 2026]),
            route('admin.welcome-desa.index'),
        ];

        foreach ($adminPages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }

        $kepalaDesaPages = [
            route('kepala-desa.dashboard'),
            route('kepala-desa.hasil-rekomendasi.index', ['tahun' => 2026]),
            route('kepala-desa.keputusan-akhir.index'),
        ];

        foreach ($kepalaDesaPages as $page) {
            $this->actingAs($kepalaDesa)->get($page)->assertOk();
        }

        $ownDraft = UsulanPembangunan::where('dusun_id', $kepalaDusun->dusun_id)
            ->where('status_usulan', UsulanPembangunan::STATUS_DIAJUKAN)
            ->first();

        if (! $ownDraft) {
            $ownDraft = UsulanPembangunan::factory()->create([
                'tahun_perencanaan_id' => $periode->id,
                'dusun_id' => $kepalaDusun->dusun_id,
                'user_id' => $kepalaDusun->id,
                'status_usulan' => UsulanPembangunan::STATUS_DIAJUKAN,
            ]);
        }

        $kepalaDusunPages = [
            route('kepala-dusun.dashboard'),
            route('kepala-dusun.usulan.index', ['tahun' => 2026]),
            route('kepala-dusun.usulan.create'),
            route('kepala-dusun.usulan.edit', $ownDraft),
        ];

        foreach ($kepalaDusunPages as $page) {
            $response = $this->actingAs($kepalaDusun)->get($page);

            $this->assertSame(200, $response->getStatusCode(), $page.' dialihkan ke '.$response->headers->get('Location'));
        }
    }
}
