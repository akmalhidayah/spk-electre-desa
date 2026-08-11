<?php

namespace Tests\Feature;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\ElectreResultDetail;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use App\Services\BudgetAllocationService;
use App\Services\ElectreService;
use App\Services\RecalculationFlagService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PartialPenilaianElectreTest extends TestCase
{
    use RefreshDatabase;

    public function test_penilaian_bertahap_dan_pengujian_a1_sampai_a4(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $periode = TahunPerencanaan::where('tahun', 2026)->firstOrFail();
        $programs = UsulanPembangunan::periode($periode->id)->diterima()->orderBy('id')->get();
        $kriterias = Kriteria::aktif()->ordered()->get();

        $this->assertCount(64, $programs);
        $this->assertSame([
            'Pembangunan Talud Pasar',
            'Pembangunan Los Pasar',
            'Rehabilitasi Posyandu',
            'Pembangunan Lanjutan Jembatan Liu Sirie',
        ], $programs->take(4)->pluck('nama_kegiatan')->all());

        $programs->take(4)->each(fn (UsulanPembangunan $program, int $index) => $program->forceFill([
            'estimasi_anggaran' => ($index + 1) * 10000000,
        ])->save());

        $scores = [
            [4, 5, 5, 5, 4, 5],
            [3, 3, 3, 4, 3, 4],
            [3, 3, 3, 3, 3, 4],
            [4, 4, 4, 4, 4, 4],
        ];
        $nilai = [];

        foreach ($programs->take(4)->values() as $programIndex => $program) {
            foreach ($kriterias->values() as $kriteriaIndex => $kriteria) {
                $nilai[$program->id][$kriteria->id] = $scores[$programIndex][$kriteriaIndex];
            }
        }

        $response = $this->actingAs($admin)->post(route('admin.penilaian.store'), [
            'tahun' => 2026,
            'nilai' => $nilai,
        ]);

        $response
            ->assertRedirect(route('admin.penilaian.index', ['tahun' => 2026]))
            ->assertSessionHas('success', 'Penilaian berhasil disimpan. 4 dari 64 program telah dinilai lengkap.');

        $this->assertDatabaseCount('penilaian_alternatifs', 24);
        $this->assertFalse(PenilaianAlternatif::whereIn('usulan_pembangunan_id', $programs->skip(4)->pluck('id'))->exists());
        $this->assertFalse(PenilaianAlternatif::where('nilai', 0)->exists());

        $reloadResponse = $this->actingAs($admin)
            ->get(route('admin.penilaian.index', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('4 dari 64 program telah dinilai lengkap.')
            ->assertSee('Pembangunan Talud Pasar');

        $reloadResponse->assertSee('value="4" selected', false);

        try {
            app(ElectreService::class)->calculate(2026, $admin->id);
            $this->fail('Perhitungan reguler seharusnya ditolak ketika penilaian belum lengkap.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('4 dari 64 program lengkap', $exception->getMessage());
        }

        $testResponse = $this->actingAs($admin)->post(route('admin.electre.process'), [
            'tahun' => 2026,
            'mode' => 'pengujian',
            'program_ids' => $programs->take(4)->pluck('id')->all(),
        ]);

        $testResponse->assertSessionHas('success', 'Pengujian alternatif terpilih berhasil diproses.');

        $calculation = ElectreCalculation::periode($periode->id)
            ->where('notes', 'like', 'JENIS_PERHITUNGAN=PENGUJIAN%')
            ->latest('id')
            ->firstOrFail();

        $testResponse->assertRedirect(route('admin.electre.show', $calculation));

        $results = $calculation->results()->ranking()->get();

        $this->assertSame(4, $calculation->total_alternatif);
        $this->assertStringContainsString('JENIS_PERHITUNGAN=PENGUJIAN', $calculation->notes);
        $this->assertSame(['A1', 'A4', 'A2', 'A3'], $results->pluck('kode_alternatif')->all());
        $this->assertSame([3, 2, 1, 0], $results->pluck('skor_dominasi')->all());
        $rankingSummary = collect($calculation->details->firstWhere('tahap', 'ranking_summary')->data);
        $metadataAlternatif = collect($calculation->details->firstWhere('tahap', 'metadata_alternatif')->data);
        $this->assertSame(10000000.0, (float) $rankingSummary->firstWhere('kode_alternatif', 'A1')['estimasi_anggaran']);
        $this->assertSame(10000000.0, (float) $metadataAlternatif->firstWhere('kode', 'A1')['estimasi_anggaran']);
        $this->assertSame(64, UsulanPembangunan::periode($periode->id)->count());

        $this->actingAs($admin)
            ->get(route('admin.electre.show', $calculation))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.hasil-rekomendasi.show', $calculation))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar')
            ->assertSee('Rp 10.000.000');

        $kepalaDesa = User::where('email', 'kepaladesa@example.com')->firstOrFail();

        $periode->forceFill([
            'pagu_anggaran' => 100000000,
        ])->save();
        $calculation->forceFill(['is_latest' => true, 'notes' => 'JENIS_PERHITUNGAN=REGULER; fixture pengujian keputusan.'])->save();
        app(RecalculationFlagService::class)->clear(2026, $calculation->id);
        $this->assertTrue(app(BudgetAllocationService::class)->isOfficialCalculation($calculation->fresh()));

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.dashboard', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar');

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.hasil-rekomendasi.show', $calculation))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar')
            ->assertSee('Rp 10.000.000');

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.create', $calculation))
            ->assertOk();

        $decisionResponse = $this->actingAs($kepalaDesa)
            ->post(route('kepala-desa.keputusan-akhir.store'), [
                'electre_calculation_id' => $calculation->id,
                'electre_result_ids' => $results->take(2)->pluck('id')->all(),
                'tanggal_keputusan' => now()->toDateString(),
                'status' => 'ditetapkan',
            ]);

        $keputusan = KeputusanAkhir::where('electre_calculation_id', $calculation->id)->firstOrFail();

        $decisionResponse
            ->assertRedirect(route('kepala-desa.keputusan-akhir.show', $keputusan))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Keputusan akhir berhasil disimpan.');

        $this->assertSame($results->first()->id, $keputusan->electre_result_id);
        $this->assertCount(2, $keputusan->details);
        $this->assertSame(KeputusanAkhir::STATUS_DITETAPKAN, $keputusan->status);
        $this->assertNotEmpty($keputusan->snapshot_data);
        $this->assertSame(10000000.0, (float) data_get($keputusan->snapshot_data, 'selected_result.estimasi_anggaran'));
        $this->assertNotEmpty($keputusan->pdf_path);
        $budgetSummary = app(BudgetAllocationService::class)->summary($periode->refresh());
        $this->assertSame(50000000.0, $budgetSummary['total_ditetapkan']);
        $this->assertSame(50000000.0, $budgetSummary['sisa_pagu']);
        $this->assertSame(100000000.0, (float) $periode->fresh()->pagu_anggaran);

        $calculation->update(['is_latest' => false]);
        $expensiveProgram = $programs[4];
        $expensiveProgram->update(['estimasi_anggaran' => 60000000]);
        $nextCalculation = ElectreCalculation::factory()->create([
            'tahun_perencanaan_id' => $periode->id,
            'status' => ElectreCalculation::STATUS_SELESAI,
            'versi' => 999,
            'is_latest' => true,
            'notes' => 'JENIS_PERHITUNGAN=REGULER',
        ]);
        $expensiveResult = ElectreResult::factory()->create([
            'electre_calculation_id' => $nextCalculation->id,
            'usulan_pembangunan_id' => $expensiveProgram->id,
            'kode_alternatif' => 'A5',
            'ranking' => 1,
        ]);
        ElectreResultDetail::create(['electre_calculation_id' => $nextCalculation->id, 'tahap' => 'ranking_summary', 'data' => [['usulan_pembangunan_id' => $expensiveProgram->id, 'estimasi_anggaran' => 60000000]]]);
        ElectreResultDetail::create(['electre_calculation_id' => $nextCalculation->id, 'tahap' => 'metadata_alternatif', 'data' => [['id' => $expensiveProgram->id, 'estimasi_anggaran' => 60000000]]]);

        $this->actingAs($kepalaDesa)->post(route('kepala-desa.keputusan-akhir.store'), [
            'electre_calculation_id' => $nextCalculation->id,
            'electre_result_ids' => [$expensiveResult->id],
            'tanggal_keputusan' => now()->toDateString(),
            'status' => 'ditetapkan',
        ])->assertSessionHas('error', fn ($message) => str_contains($message, 'melebihi sisa pagu'));
        $this->assertDatabaseMissing('keputusan_akhirs', ['electre_calculation_id' => $nextCalculation->id]);

        $this->actingAs($admin)->put(route('admin.tahun-perencanaan.update', $periode), [
            'tahun' => 2026,
            'nama_periode' => $periode->nama_periode,
            'pagu_anggaran' => 40000000,
        ])->assertSessionHasErrors('pagu_anggaran');
        $this->assertSame(100000000.0, (float) $periode->fresh()->pagu_anggaran);

        $programs->first()->update(['nama_kegiatan' => 'Nama Live Berubah', 'estimasi_anggaran' => 99000000]);
        $periode->forceFill(['pagu_anggaran' => 200000000])->save();
        $this->assertSame('Pembangunan Talud Pasar', data_get($keputusan->fresh()->snapshot_data, 'selected_results.0.nama_program'));
        $this->assertSame(10000000.0, (float) data_get($keputusan->snapshot_data, 'selected_results.0.estimasi_anggaran'));
        $this->actingAs($kepalaDesa)->get(route('kepala-desa.keputusan-akhir.pdf', $keputusan))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
