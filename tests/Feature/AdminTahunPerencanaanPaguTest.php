<?php

namespace Tests\Feature;

use App\Models\ElectreCalculation;
use App\Models\KeputusanAkhir;
use App\Models\KeputusanAkhirDetail;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTahunPerencanaanPaguTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_dan_memperbarui_pagu_tahun_aktif_tanpa_mengubah_status_periode(): void
    {
        $admin = User::factory()->create();
        $periode = TahunPerencanaan::factory()->create([
            'tahun' => 2026,
            'pagu_anggaran' => 250000000,
            'is_active' => true,
            'is_locked' => true,
            'perlu_hitung_ulang' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tahun-perencanaan.index'))
            ->assertOk()
            ->assertSee('Pagu Anggaran Pembangunan Tahun 2026')
            ->assertSee('Tahun &amp; Pagu Anggaran', false)
            ->assertSee('name="pagu_anggaran"', false)
            ->assertSee('value="250000000.00"', false)
            ->assertSee('Simpan Pagu');

        $this->actingAs($admin)
            ->patch(route('admin.tahun-perencanaan.update-pagu', $periode), [
                'pagu_anggaran' => 500000000,
            ])
            ->assertRedirect(route('admin.tahun-perencanaan.index'))
            ->assertSessionHas('success', 'Pagu anggaran pembangunan tahun 2026 berhasil diperbarui.');

        $periode->refresh();
        $this->assertSame(500000000.0, (float) $periode->pagu_anggaran);
        $this->assertSame(2026, $periode->tahun);
        $this->assertTrue($periode->is_active);
        $this->assertTrue($periode->is_locked);
        $this->assertFalse($periode->perlu_hitung_ulang);
    }

    public function test_pagu_tidak_boleh_lebih_kecil_dari_anggaran_yang_sudah_ditetapkan_tetapi_boleh_dinaikkan(): void
    {
        $admin = User::factory()->create();
        $periode = TahunPerencanaan::factory()->create(['tahun' => 2026, 'pagu_anggaran' => 500000000, 'is_active' => true]);
        $program = UsulanPembangunan::factory()->create(['tahun_perencanaan_id' => $periode->id, 'estimasi_anggaran' => 350000000]);
        $calculation = ElectreCalculation::factory()->create(['tahun_perencanaan_id' => $periode->id, 'calculated_by' => $admin->id]);
        $decision = KeputusanAkhir::create([
            'electre_calculation_id' => $calculation->id,
            'usulan_pembangunan_id' => $program->id,
            'tahun_perencanaan_id' => $periode->id,
            'status' => KeputusanAkhir::STATUS_DITETAPKAN,
        ]);
        KeputusanAkhirDetail::create([
            'keputusan_akhir_id' => $decision->id,
            'usulan_pembangunan_id' => $program->id,
            'nama_program_snapshot' => $program->nama_kegiatan,
            'estimasi_anggaran_snapshot' => 350000000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.tahun-perencanaan.update-pagu', $periode), ['pagu_anggaran' => 300000000])
            ->assertSessionHasErrors([
                'pagu_anggaran' => 'Pagu anggaran tidak dapat lebih kecil dari total anggaran program yang telah ditetapkan sebesar Rp350.000.000.',
            ]);
        $this->assertSame(500000000.0, (float) $periode->fresh()->pagu_anggaran);

        $this->actingAs($admin)
            ->patch(route('admin.tahun-perencanaan.update-pagu', $periode), ['pagu_anggaran' => 600000000])
            ->assertSessionHasNoErrors();
        $this->assertSame(600000000.0, (float) $periode->fresh()->pagu_anggaran);
        $this->assertFalse($periode->fresh()->perlu_hitung_ulang);
        $this->assertSame($calculation->id, $calculation->fresh()->id);
    }

    public function test_index_tidak_error_saat_belum_ada_tahun_aktif(): void
    {
        $admin = User::factory()->create();
        TahunPerencanaan::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.tahun-perencanaan.index'))
            ->assertOk()
            ->assertSee('Belum ada tahun perencanaan aktif. Aktifkan salah satu tahun terlebih dahulu untuk mengatur pagu anggaran.')
            ->assertDontSee('Simpan Pagu');
    }
}
