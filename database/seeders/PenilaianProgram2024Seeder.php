<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenilaianProgram2024Seeder extends Seeder
{
    private const SUMBER_DOKUMEN = 'Daftar Usulan Historis Desa Barambang Tahun 2024';

    public function run(): void
    {
        $periode = TahunPerencanaan::where('tahun', 2024)->first();

        if (! $periode) {
            throw new RuntimeException('Periode 2024 belum tersedia. Jalankan Program2024Seeder terlebih dahulu.');
        }

        $programRecords = UsulanPembangunan::query()
            ->where('tahun_perencanaan_id', $periode->id)
            ->where('sumber_dokumen', self::SUMBER_DOKUMEN)
            ->get();
        $programs = $programRecords
            ->keyBy(fn (UsulanPembangunan $program): int => (int) $program->nomor_dokumen);
        $kriterias = Kriteria::query()
            ->whereIn('kode', ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])
            ->get()
            ->keyBy('kode');
        $nilai = $this->nilai();

        if ($programRecords->count() !== 71
            || $programs->count() !== 71
            || $programs->keys()->sort()->values()->all() !== range(1, 71)) {
            throw new RuntimeException('Data program sumber 2024 harus tepat 71 record dengan nomor dokumen lengkap 1 sampai 71.');
        }

        if ($kriterias->count() !== 6) {
            throw new RuntimeException('Kriteria aktif C1 sampai C6 belum lengkap.');
        }

        if (count($nilai) !== 71) {
            throw new RuntimeException('Matriks nilai sintetis 2024 harus tepat 71 baris.');
        }

        $adminId = User::where('email', 'admin@example.com')->value('id');
        $kodeKriteria = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];

        DB::transaction(function () use ($periode, $programs, $kriterias, $nilai, $adminId, $kodeKriteria): void {
            foreach ($nilai as $index => $nilaiProgram) {
                $nomorSumber = $index + 1;
                $program = $programs[$nomorSumber];

                foreach ($kodeKriteria as $indexKriteria => $kode) {
                    PenilaianAlternatif::updateOrCreate(
                        [
                            'tahun_perencanaan_id' => $periode->id,
                            'usulan_pembangunan_id' => $program->id,
                            'kriteria_id' => $kriterias[$kode]->id,
                        ],
                        [
                            'nilai' => $nilaiProgram[$indexKriteria],
                            'keterangan' => 'Data dummy/sintetis untuk pengujian proses ELECTRE Tahun 2024.',
                            'sumber_data' => 'Matriks penilaian sintetis program 2024',
                            'created_by' => $adminId,
                        ],
                    );
                }
            }
        });

        $this->command?->info('Penilaian 71 program Tahun 2024 berhasil disimpan (426 nilai C1-C6).');
    }

    /** @return array<int, array<int, int>> */
    private function nilai(): array
    {
        return [
            [4, 4, 3, 5, 5, 4],
            [4, 2, 1, 4, 3, 5],
            [2, 4, 1, 4, 3, 5],
            [3, 2, 4, 3, 3, 5],
            [2, 4, 3, 4, 1, 3],
            [5, 2, 5, 5, 3, 4],
            [4, 5, 2, 4, 4, 4],
            [2, 1, 3, 4, 3, 2],
            [5, 5, 4, 4, 3, 3],
            [5, 3, 4, 5, 5, 2],
            [4, 3, 3, 5, 4, 2],
            [5, 5, 5, 3, 3, 5],
            [4, 2, 5, 5, 4, 2],
            [2, 4, 4, 4, 3, 2],
            [4, 5, 3, 3, 4, 5],
            [2, 5, 2, 2, 2, 3],
            [3, 4, 5, 4, 5, 2],
            [4, 4, 3, 3, 3, 2],
            [4, 5, 5, 4, 4, 2],
            [4, 3, 5, 5, 5, 5],
            [4, 4, 4, 4, 4, 3],
            [3, 4, 2, 5, 2, 3],
            [1, 2, 4, 3, 5, 2],
            [3, 3, 4, 2, 5, 2],
            [2, 3, 5, 4, 4, 5],
            [4, 4, 4, 2, 4, 3],
            [2, 3, 2, 5, 4, 3],
            [5, 1, 4, 5, 2, 1],
            [2, 5, 4, 2, 4, 3],
            [5, 2, 5, 4, 3, 5],
            [5, 4, 4, 5, 3, 4],
            [4, 5, 3, 2, 2, 3],
            [5, 5, 5, 1, 3, 5],
            [5, 4, 4, 4, 5, 4],
            [4, 4, 2, 4, 5, 4],
            [2, 5, 3, 1, 4, 2],
            [4, 2, 2, 3, 2, 2],
            [5, 4, 2, 4, 2, 5],
            [5, 5, 4, 3, 4, 5],
            [5, 3, 4, 2, 3, 5],
            [4, 4, 4, 4, 4, 3],
            [4, 2, 3, 5, 5, 5],
            [4, 2, 3, 4, 5, 3],
            [2, 4, 4, 2, 4, 3],
            [3, 5, 5, 4, 4, 3],
            [5, 2, 3, 2, 3, 3],
            [5, 4, 1, 3, 5, 4],
            [4, 5, 4, 3, 2, 4],
            [3, 4, 1, 2, 4, 2],
            [2, 4, 3, 5, 2, 4],
            [5, 3, 3, 4, 3, 4],
            [4, 4, 1, 4, 4, 4],
            [5, 3, 4, 4, 3, 3],
            [4, 4, 1, 3, 4, 4],
            [3, 3, 1, 4, 4, 5],
            [3, 3, 2, 5, 2, 2],
            [4, 4, 2, 5, 1, 4],
            [4, 3, 4, 2, 2, 2],
            [4, 4, 3, 2, 2, 2],
            [1, 3, 4, 3, 2, 5],
            [2, 2, 4, 3, 5, 3],
            [4, 5, 4, 4, 5, 4],
            [2, 5, 4, 3, 4, 4],
            [4, 1, 3, 2, 4, 3],
            [5, 5, 4, 1, 3, 3],
            [4, 4, 4, 4, 4, 5],
            [2, 4, 2, 4, 3, 3],
            [1, 2, 2, 3, 3, 2],
            [3, 4, 2, 2, 4, 3],
            [4, 2, 2, 3, 3, 2],
            [2, 3, 4, 5, 4, 5],
        ];
    }
}
