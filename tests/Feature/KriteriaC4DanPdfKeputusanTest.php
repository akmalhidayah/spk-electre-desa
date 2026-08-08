<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Database\Seeders\KriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KriteriaC4DanPdfKeputusanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kriteria_seeder_menyimpan_c4_sebagai_kondisi_objek_pembangunan(): void
    {
        $this->seed(KriteriaSeeder::class);

        $this->assertDatabaseHas('kriterias', [
            'kode' => 'C4',
            'nama_kriteria' => 'Kondisi Objek Pembangunan',
            'deskripsi' => 'Menilai kondisi aktual objek atau layanan yang menjadi sasaran pembangunan.',
            'bobot' => 15,
            'tipe' => Kriteria::TIPE_BENEFIT,
            'status' => Kriteria::STATUS_AKTIF,
        ]);
    }

    public function test_kepala_desa_dapat_mencetak_pdf_penetapan_hasil(): void
    {
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $admin = User::factory()->create();
        $dusun = Dusun::factory()->create(['nama_dusun' => 'Dusun Uji']);
        $periode = TahunPerencanaan::factory()->create(['tahun' => 2026]);
        $program = UsulanPembangunan::factory()->create(['tahun_perencanaan_id' => $periode->id, 'dusun_id' => $dusun->id]);
        $calculation = ElectreCalculation::factory()->create([
            'tahun_perencanaan_id' => $periode->id,
            'status' => ElectreCalculation::STATUS_SELESAI,
            'calculated_by' => $admin->id,
            'calculated_at' => now(),
            'total_alternatif' => 1,
            'total_kriteria' => 6,
        ]);
        $result = ElectreResult::factory()->create([
            'electre_calculation_id' => $calculation->id,
            'usulan_pembangunan_id' => $program->id,
            'kode_alternatif' => 'A1',
            'nama_program_snapshot' => $program->nama_kegiatan,
            'lokasi_snapshot' => $program->lokasi_kegiatan,
            'nama_dusun_snapshot' => $dusun->nama_dusun,
            'ranking' => 1,
        ]);
        $keputusan = KeputusanAkhir::create([
            'electre_calculation_id' => $calculation->id,
            'electre_result_id' => $result->id,
            'usulan_pembangunan_id' => $program->id,
            'tahun_perencanaan_id' => $periode->id,
            'nomor_keputusan' => '01/KPTS/TEST',
            'tanggal_keputusan' => now()->toDateString(),
            'status' => KeputusanAkhir::STATUS_DITETAPKAN,
            'ditetapkan_oleh' => $kepalaDesa->id,
        ]);

        $this->seed(KriteriaSeeder::class);

        $response = $this
            ->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.show', [
                'keputusanAkhir' => $keputusan,
                'pdf' => 1,
            ]));

        $response->assertRedirect(route('kepala-desa.keputusan-akhir.pdf', $keputusan));

        $response = $this
            ->actingAs($kepalaDesa)
            ->get(route('kepala-desa.keputusan-akhir.pdf', $keputusan));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $keputusan->refresh();

        $this->assertNotNull($keputusan->snapshot_data);
        $this->assertNotNull($keputusan->snapshotted_at);
        $this->assertNotNull($keputusan->pdf_path);
        $this->assertNotNull($keputusan->pdf_hash);
    }
}
