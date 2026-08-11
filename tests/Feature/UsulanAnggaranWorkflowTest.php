<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsulanAnggaranWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_menyimpan_usulan_dengan_anggaran_dan_status_internal_diterima(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $dusun = Dusun::firstOrFail();

        $this->actingAs($admin)->post(route('admin.usulan.store'), $this->payload($dusun, 'Program Admin'))
            ->assertRedirect(route('admin.usulan.index'));

        $usulan = UsulanPembangunan::where('nama_kegiatan', 'Program Admin')->firstOrFail();
        $this->assertSame('50000000.00', $usulan->estimasi_anggaran);
        $this->assertSame(UsulanPembangunan::STATUS_DITERIMA, $usulan->status_usulan);
        $this->assertNull($usulan->catatan_admin);
        $this->assertTrue(TahunPerencanaan::where('tahun', 2026)->firstOrFail()->perlu_hitung_ulang);
    }

    public function test_kepala_dusun_dapat_membuat_mengedit_dan_menghapus_usulan_diterima_miliknya(): void
    {
        $this->seed(DatabaseSeeder::class);
        $kepalaDusun = User::where('email', 'katute@example.com')->firstOrFail();
        $dusun = $kepalaDusun->dusun;

        $this->actingAs($kepalaDusun)->post(route('kepala-dusun.usulan.store'), $this->payload($dusun, 'Program Dusun'))
            ->assertRedirect(route('kepala-dusun.usulan.index'));

        $usulan = UsulanPembangunan::where('nama_kegiatan', 'Program Dusun')->firstOrFail();
        $this->assertSame($dusun->id, $usulan->dusun_id);
        $this->assertSame(UsulanPembangunan::STATUS_DITERIMA, $usulan->status_usulan);
        $this->assertNull($usulan->catatan_admin);

        $updatePayload = $this->payload($dusun, 'Program Dusun Diperbarui');
        $updatePayload['estimasi_anggaran'] = 75000000;

        $this->actingAs($kepalaDusun)->put(route('kepala-dusun.usulan.update', $usulan), $updatePayload)
            ->assertRedirect(route('kepala-dusun.usulan.index'));

        $this->assertSame('75000000.00', $usulan->refresh()->estimasi_anggaran);
        $this->assertSame(UsulanPembangunan::STATUS_DITERIMA, $usulan->status_usulan);

        $this->actingAs($kepalaDusun)->delete(route('kepala-dusun.usulan.destroy', $usulan))
            ->assertRedirect(route('kepala-dusun.usulan.index'));

        $this->assertSoftDeleted($usulan);
    }

    public function test_kepala_dusun_tidak_dapat_mengubah_atau_menghapus_usulan_dusun_lain(): void
    {
        $this->seed(DatabaseSeeder::class);
        $kepalaDusun = User::where('email', 'katute@example.com')->firstOrFail();
        $usulanLain = UsulanPembangunan::where('dusun_id', '!=', $kepalaDusun->dusun_id)->firstOrFail();

        $this->actingAs($kepalaDusun)->get(route('kepala-dusun.usulan.edit', $usulanLain))->assertForbidden();
        $this->actingAs($kepalaDusun)->delete(route('kepala-dusun.usulan.destroy', $usulanLain))->assertForbidden();
    }

    public function test_halaman_usulan_menampilkan_jumlah_anggaran_tanpa_status_workflow(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $kepalaDusun = User::where('email', 'katute@example.com')->firstOrFail();
        $usulan = UsulanPembangunan::where('dusun_id', $kepalaDusun->dusun_id)->firstOrFail();
        $usulan->forceFill(['estimasi_anggaran' => 50000000])->save();

        $this->actingAs($admin)->get(route('admin.usulan.index', ['tahun' => $usulan->tahun, 'q' => $usulan->nama_kegiatan]))
            ->assertOk()->assertSee('Rp 50.000.000')->assertDontSee('Status &amp; Catatan', false);

        $this->actingAs($kepalaDusun)->get(route('kepala-dusun.usulan.index', ['tahun' => $usulan->tahun, 'q' => $usulan->nama_kegiatan]))
            ->assertOk()->assertSee('Rp 50.000.000')->assertDontSee('Terkunci');
    }

    private function payload(Dusun $dusun, string $nama): array
    {
        return [
            'tipe_usulan' => UsulanPembangunan::TIPE_DUSUN,
            'dusun_id' => $dusun->id,
            'tahun' => 2026,
            'nama_kegiatan' => $nama,
            'lokasi_kegiatan' => $dusun->nama_dusun,
            'estimasi_anggaran' => 50000000,
        ];
    }
}
