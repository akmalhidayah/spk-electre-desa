<?php

namespace Tests\Feature;

use App\Models\ElectreCalculation;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use App\Services\ElectreService;
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
        $this->assertSame(64, UsulanPembangunan::periode($periode->id)->count());

        $this->actingAs($admin)
            ->get(route('admin.electre.show', $calculation))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.hasil-rekomendasi.show', $calculation))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar');

        $kepalaDesa = User::where('email', 'kepaladesa@example.com')->firstOrFail();

        $calculation->forceFill(['is_latest' => true])->save();

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.dashboard', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar');

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.hasil-rekomendasi.show', $calculation))
            ->assertOk()
            ->assertSee('Pembangunan Talud Pasar');

        $this->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.create', $calculation))
            ->assertOk();

        $decisionResponse = $this->actingAs($kepalaDesa)
            ->post(route('kepala-desa.keputusan-akhir.store'), [
                'electre_calculation_id' => $calculation->id,
                'electre_result_id' => $results->first()->id,
                'tanggal_keputusan' => now()->toDateString(),
                'status' => 'ditetapkan',
            ]);

        $keputusan = KeputusanAkhir::where('electre_calculation_id', $calculation->id)->firstOrFail();

        $decisionResponse
            ->assertRedirect(route('kepala-desa.keputusan-akhir.show', $keputusan))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Keputusan akhir berhasil disimpan.');

        $this->assertSame($results->first()->id, $keputusan->electre_result_id);
        $this->assertSame(KeputusanAkhir::STATUS_DITETAPKAN, $keputusan->status);
        $this->assertNotEmpty($keputusan->snapshot_data);
        $this->assertNotEmpty($keputusan->pdf_path);
    }
}
