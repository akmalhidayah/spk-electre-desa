<?php

namespace Tests\Feature;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\ElectreResultDetail;
use App\Models\KeputusanAkhir;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use App\Services\BudgetAllocationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulasi_melewati_program_mahal_dan_mencoba_ranking_berikutnya(): void
    {
        $this->seed(DatabaseSeeder::class);
        $periode = TahunPerencanaan::where('tahun', 2026)->firstOrFail();
        $periode->update(['pagu_anggaran' => 500]);
        $programs = UsulanPembangunan::periode($periode->id)->take(3)->get();
        foreach ([200, 400, 100] as $index => $amount) {
            $programs[$index]->update(['estimasi_anggaran' => $amount]);
        }

        $calculation = ElectreCalculation::factory()->create([
            'tahun_perencanaan_id' => $periode->id,
            'status' => ElectreCalculation::STATUS_SELESAI,
            'is_latest' => true,
            'notes' => 'JENIS_PERHITUNGAN=REGULER',
        ]);
        $ranking = [];
        $metadata = [];
        foreach ($programs as $index => $program) {
            $result = ElectreResult::factory()->create([
                'electre_calculation_id' => $calculation->id,
                'usulan_pembangunan_id' => $program->id,
                'kode_alternatif' => 'A'.($index + 1),
                'ranking' => $index + 1,
            ]);
            $ranking[] = ['usulan_pembangunan_id' => $program->id, 'kode_alternatif' => $result->kode_alternatif, 'estimasi_anggaran' => (float) $program->estimasi_anggaran];
            $metadata[] = ['id' => $program->id, 'kode' => $result->kode_alternatif, 'estimasi_anggaran' => (float) $program->estimasi_anggaran];
        }
        ElectreResultDetail::create(['electre_calculation_id' => $calculation->id, 'tahap' => 'ranking_summary', 'data' => $ranking]);
        ElectreResultDetail::create(['electre_calculation_id' => $calculation->id, 'tahap' => 'metadata_alternatif', 'data' => $metadata]);

        $simulation = app(BudgetAllocationService::class)->simulate($periode->refresh(), $calculation->refresh());

        $this->assertSame(['terakomodasi', 'belum_terakomodasi', 'terakomodasi'], $simulation['results']->pluck('status_anggaran')->all());
        $this->assertSame(200.0, $simulation['summary']['sisa_simulasi']);
        $this->assertSame(500.0, (float) $periode->fresh()->pagu_anggaran);
        $this->assertTrue(app(BudgetAllocationService::class)->isOfficialCalculation($calculation));
        $calculation->update(['notes' => 'JENIS_PERHITUNGAN=PENGUJIAN']);
        $this->assertFalse(app(BudgetAllocationService::class)->isOfficialCalculation($calculation->refresh()));

        $periode->update(['pagu_anggaran' => null]);
        $withoutBudget = app(BudgetAllocationService::class)->simulate($periode->refresh(), $calculation->refresh());
        $this->assertNull($withoutBudget['summary']['sisa_pagu']);
        $this->assertSame(['pagu_belum_diatur', 'pagu_belum_diatur', 'pagu_belum_diatur'], $withoutBudget['results']->pluck('status_anggaran')->all());
    }

    public function test_keputusan_draft_tidak_mengurangi_pagu(): void
    {
        $this->seed(DatabaseSeeder::class);
        $periode = TahunPerencanaan::where('tahun', 2026)->firstOrFail();
        $periode->update(['pagu_anggaran' => 500]);
        $program = UsulanPembangunan::periode($periode->id)->firstOrFail();
        $program->update(['estimasi_anggaran' => 150]);
        $calculation = ElectreCalculation::factory()->create(['tahun_perencanaan_id' => $periode->id]);
        $result = ElectreResult::factory()->create(['electre_calculation_id' => $calculation->id, 'usulan_pembangunan_id' => $program->id]);
        $decision = KeputusanAkhir::create([
            'electre_calculation_id' => $calculation->id,
            'electre_result_id' => $result->id,
            'usulan_pembangunan_id' => $program->id,
            'tahun_perencanaan_id' => $periode->id,
            'status' => KeputusanAkhir::STATUS_DRAFT,
        ]);
        $decision->details()->create(['electre_result_id' => $result->id, 'usulan_pembangunan_id' => $program->id, 'nama_program_snapshot' => $program->nama_kegiatan, 'estimasi_anggaran_snapshot' => 150]);

        $summary = app(BudgetAllocationService::class)->summary($periode->refresh());
        $this->assertSame(0.0, $summary['total_ditetapkan']);
        $this->assertSame(500.0, $summary['sisa_pagu']);
    }
}
