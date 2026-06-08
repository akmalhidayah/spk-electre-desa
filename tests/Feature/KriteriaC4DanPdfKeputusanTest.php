<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\User;
use Database\Seeders\KriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KriteriaC4DanPdfKeputusanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kriteria_seeder_menyimpan_c4_sebagai_kondisi_jalan(): void
    {
        $this->seed(KriteriaSeeder::class);

        $this->assertDatabaseHas('kriterias', [
            'kode' => 'C4',
            'nama_kriteria' => 'Kondisi Jalan',
            'deskripsi' => 'Menggambarkan kondisi akses jalan pada masing-masing dusun sebagai pertimbangan prioritas pembangunan. Semakin buruk kondisi jalan, maka semakin tinggi kebutuhan pembangunan.',
            'bobot' => 15,
            'tipe' => Kriteria::TIPE_BENEFIT,
            'status' => Kriteria::STATUS_AKTIF,
        ]);
    }

    public function test_kepala_desa_dapat_mencetak_pdf_penetapan_hasil(): void
    {
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $admin = User::factory()->create();
        $dusun = Dusun::factory()->create([
            'kode_alternatif' => 'A1',
            'nama_dusun' => 'Dusun Uji',
        ]);
        $calculation = ElectreCalculation::factory()->create([
            'status' => ElectreCalculation::STATUS_SELESAI,
            'calculated_by' => $admin->id,
            'calculated_at' => now(),
            'total_alternatif' => 1,
            'total_kriteria' => 6,
        ]);
        $result = ElectreResult::factory()->create([
            'electre_calculation_id' => $calculation->id,
            'dusun_id' => $dusun->id,
            'ranking' => 1,
        ]);
        $keputusan = KeputusanAkhir::create([
            'electre_calculation_id' => $calculation->id,
            'electre_result_id' => $result->id,
            'dusun_id' => $dusun->id,
            'tahun' => $calculation->tahun,
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

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
