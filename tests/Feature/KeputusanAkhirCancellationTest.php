<?php

namespace Tests\Feature;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use App\Services\BudgetAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KeputusanAkhirCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_desa_membatalkan_keputusan_multi_program_dan_pagu_kembali_secara_computed(): void
    {
        Storage::fake('public');
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $periode = TahunPerencanaan::factory()->create([
            'tahun' => 2026,
            'pagu_anggaran' => 500000000,
            'is_active' => true,
        ]);
        [$decision, $calculation, $programs] = $this->createDecision($periode, $kepalaDesa, [150000000, 200000000], 1);
        $decision->update(['pdf_path' => 'keputusan-akhir/2026/fixture.pdf']);
        Storage::disk('public')->put($decision->pdf_path, 'fixture');

        $before = app(BudgetAllocationService::class)->summary($periode);
        $this->assertSame(350000000.0, $before['total_ditetapkan']);
        $this->assertSame(150000000.0, $before['sisa_pagu']);
        $this->assertCount(2, $decision->details);

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.show', $decision))
            ->assertOk()
            ->assertSee('Batalkan Keputusan')
            ->assertSee('2 program dengan total anggaran Rp350.000.000');

        $response = $this->actingAs($kepalaDesa)
            ->delete(route('kepala-desa.keputusan-akhir.destroy', $decision));

        $response
            ->assertRedirect(route('kepala-desa.keputusan-akhir.index'))
            ->assertSessionHas('success', 'Keputusan berhasil dibatalkan. Alokasi anggaran telah diperbarui.');
        $this->assertDatabaseMissing('keputusan_akhirs', ['id' => $decision->id]);
        $this->assertDatabaseMissing('keputusan_akhir_details', ['keputusan_akhir_id' => $decision->id]);
        $this->assertDatabaseHas('electre_calculations', ['id' => $calculation->id]);
        foreach ($programs as $program) {
            $this->assertDatabaseHas('electre_results', ['electre_calculation_id' => $calculation->id, 'usulan_pembangunan_id' => $program->id]);
        }
        Storage::disk('public')->assertMissing('keputusan-akhir/2026/fixture.pdf');

        $after = app(BudgetAllocationService::class)->summary($periode->refresh());
        $this->assertSame(500000000.0, (float) $periode->pagu_anggaran);
        $this->assertSame(0.0, $after['total_ditetapkan']);
        $this->assertSame(500000000.0, $after['sisa_pagu']);

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.pdf', $decision->id))
            ->assertNotFound();
    }

    public function test_hanya_keputusan_yang_dipilih_dilepas_dan_dashboard_serta_rekomendasi_langsung_konsisten(): void
    {
        $admin = User::factory()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $periode = TahunPerencanaan::factory()->create(['tahun' => 2026, 'pagu_anggaran' => 500000000, 'is_active' => true]);
        [$decisionA, $calculationA] = $this->createDecision($periode, $kepalaDesa, [200000000], 1, false);
        [$decisionB] = $this->createDecision($periode, $kepalaDesa, [100000000], 2);

        $this->assertSame(300000000.0, app(BudgetAllocationService::class)->summary($periode)['total_ditetapkan']);
        $this->actingAs($admin)
            ->get(route('admin.hasil-rekomendasi.show', $calculationA))
            ->assertOk()
            ->assertSee('Sudah Ditetapkan');

        $this->actingAs($kepalaDesa)->delete(route('kepala-desa.keputusan-akhir.destroy', $decisionA))->assertRedirect();

        $summary = app(BudgetAllocationService::class)->summary($periode->refresh());
        $this->assertDatabaseHas('keputusan_akhirs', ['id' => $decisionB->id]);
        $this->assertSame(100000000.0, $summary['total_ditetapkan']);
        $this->assertSame(400000000.0, $summary['sisa_pagu']);
        $recalculatedResult = app(BudgetAllocationService::class)->simulate($periode, $calculationA)['results']->first();
        $this->assertNotSame('ditetapkan', $recalculatedResult->status_anggaran);
        $this->assertNotSame('Sudah Ditetapkan', $recalculatedResult->label_anggaran);
        $this->actingAs($admin)
            ->get(route('admin.hasil-rekomendasi.show', $calculationA))
            ->assertOk()
            ->assertDontSee('>Sudah Ditetapkan<', false);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Rp 400.000.000');
        $this->actingAs($kepalaDesa)->get(route('kepala-desa.dashboard'))->assertOk()->assertSee('Rp 400.000.000');
    }

    public function test_endpoint_pembatalan_hanya_dapat_digunakan_kepala_desa(): void
    {
        $admin = User::factory()->create();
        $kepalaDusun = User::factory()->kepalaDusun()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $periode = TahunPerencanaan::factory()->create(['pagu_anggaran' => 500000000]);
        [$decision] = $this->createDecision($periode, $kepalaDesa, [100000000]);

        $this->actingAs($admin)->delete(route('kepala-desa.keputusan-akhir.destroy', $decision))->assertForbidden();
        $this->actingAs($kepalaDusun)->delete(route('kepala-desa.keputusan-akhir.destroy', $decision))->assertForbidden();
        $this->assertDatabaseHas('keputusan_akhirs', ['id' => $decision->id]);

        $this->actingAs($kepalaDesa)->delete(route('kepala-desa.keputusan-akhir.destroy', $decision))->assertRedirect();
        $this->assertDatabaseMissing('keputusan_akhirs', ['id' => $decision->id]);
    }

    public function test_calculation_terlindungi_sampai_keputusan_dibatalkan_dan_latest_history_dipulihkan_dengan_aman(): void
    {
        $admin = User::factory()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $periode = TahunPerencanaan::factory()->create([
            'tahun' => 2026,
            'pagu_anggaran' => 500000000,
            'perlu_hitung_ulang' => true,
        ]);
        $previous = ElectreCalculation::factory()->create([
            'tahun_perencanaan_id' => $periode->id,
            'calculated_by' => $admin->id,
            'status' => ElectreCalculation::STATUS_SELESAI,
            'versi' => 1,
            'is_latest' => false,
            'notes' => 'JENIS_PERHITUNGAN=REGULER',
        ]);
        [$decision, $latest] = $this->createDecision($periode, $kepalaDesa, [100000000], 2);
        $periode->update(['last_electre_calculation_id' => $latest->id]);

        $this->actingAs($admin)
            ->delete(route('admin.electre.destroy', $latest))
            ->assertSessionHas('error', 'Hasil perhitungan tidak dapat dihapus karena masih digunakan pada Keputusan Akhir. Batalkan Keputusan Akhir terlebih dahulu.');
        $this->assertDatabaseHas('electre_calculations', ['id' => $latest->id]);
        $this->assertDatabaseHas('keputusan_akhirs', ['id' => $decision->id]);

        $this->actingAs($kepalaDesa)->delete(route('kepala-desa.keputusan-akhir.destroy', $decision))->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.electre.destroy', $latest))->assertRedirect(route('admin.electre.index'));

        $this->assertDatabaseMissing('electre_calculations', ['id' => $latest->id]);
        $this->assertTrue($previous->fresh()->is_latest);
        $this->assertSame($previous->id, $periode->fresh()->last_electre_calculation_id);
        $this->assertFalse(app(BudgetAllocationService::class)->isOfficialCalculation($previous->fresh()));
    }

    /**
     * @param  list<int|float>  $amounts
     * @return array{KeputusanAkhir, ElectreCalculation, Collection<int, UsulanPembangunan>}
     */
    private function createDecision(TahunPerencanaan $periode, User $kepalaDesa, array $amounts, int $version = 1, bool $latest = true): array
    {
        if ($latest) {
            ElectreCalculation::where('tahun_perencanaan_id', $periode->id)->update(['is_latest' => false]);
        }

        $calculation = ElectreCalculation::factory()->create([
            'tahun_perencanaan_id' => $periode->id,
            'calculated_by' => $kepalaDesa->id,
            'status' => ElectreCalculation::STATUS_SELESAI,
            'versi' => $version,
            'is_latest' => $latest,
            'notes' => 'JENIS_PERHITUNGAN=REGULER',
            'calculated_at' => now(),
        ]);
        $programs = collect();
        $results = collect();

        foreach ($amounts as $index => $amount) {
            $program = UsulanPembangunan::factory()->create([
                'tahun_perencanaan_id' => $periode->id,
                'estimasi_anggaran' => $amount,
            ]);
            $result = ElectreResult::factory()->create([
                'electre_calculation_id' => $calculation->id,
                'usulan_pembangunan_id' => $program->id,
                'kode_alternatif' => 'A'.($index + 1).'-'.$calculation->id,
                'ranking' => $index + 1,
            ]);
            $programs->push($program);
            $results->push($result);
        }

        $firstResult = $results->first();
        $decision = KeputusanAkhir::create([
            'electre_calculation_id' => $calculation->id,
            'electre_result_id' => $firstResult->id,
            'usulan_pembangunan_id' => $firstResult->usulan_pembangunan_id,
            'tahun_perencanaan_id' => $periode->id,
            'status' => KeputusanAkhir::STATUS_DITETAPKAN,
            'ditetapkan_oleh' => $kepalaDesa->id,
        ]);

        foreach ($results as $index => $result) {
            $program = $programs[$index];
            $decision->details()->create([
                'electre_result_id' => $result->id,
                'usulan_pembangunan_id' => $program->id,
                'kode_alternatif_snapshot' => $result->kode_alternatif,
                'nama_program_snapshot' => $program->nama_kegiatan,
                'ranking_snapshot' => $result->ranking,
                'estimasi_anggaran_snapshot' => $amounts[$index],
            ]);
        }

        return [$decision->refresh(), $calculation->refresh(), $programs];
    }
}
