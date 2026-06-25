<?php

namespace App\Services;

use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\ElectreResultDetail;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ElectreService
{
    public function calculate(int $tahun, ?int $userId = null): ElectreCalculation
    {
        try {
            return DB::transaction(function () use ($tahun, $userId): ElectreCalculation {
                $dusuns = $this->getActiveDusuns();
                $kriterias = $this->getActiveKriterias();
                $this->validateInputs($tahun, $dusuns, $kriterias);

                $decisionMatrix = $this->buildDecisionMatrix($tahun, $dusuns, $kriterias);
                $normalization = $this->normalizeMatrix($decisionMatrix, $kriterias);
                $weightedMatrix = $this->buildWeightedMatrix($normalization['matrix'], $kriterias);
                $sets = $this->buildConcordanceDiscordanceSets($weightedMatrix, $dusuns, $kriterias);
                $concordanceMatrix = $this->buildConcordanceMatrix($sets['concordance_ids'], $dusuns, $kriterias);
                $discordanceMatrix = $this->buildDiscordanceMatrix($sets['discordance_ids'], $weightedMatrix, $dusuns, $kriterias);
                $thresholds = $this->calculateThresholds($concordanceMatrix, $discordanceMatrix, $dusuns->count());
                $dominantMatrices = $this->buildDominantMatrices($concordanceMatrix, $discordanceMatrix, $thresholds, $dusuns);
                $aggregateDominance = $this->buildAggregateDominanceMatrix($dominantMatrices['concordance'], $dominantMatrices['discordance'], $dusuns);
                $ranking = $this->buildRanking($aggregateDominance, $weightedMatrix, $dusuns);
                $versi = ((int) ElectreCalculation::tahun($tahun)->max('versi')) + 1;

                ElectreCalculation::tahun($tahun)->update(['is_latest' => false]);

                $calculation = ElectreCalculation::create([
                    'kode_perhitungan' => $this->generateCalculationCode($tahun),
                    'tahun' => $tahun,
                    'judul' => "Perhitungan ELECTRE Tahun {$tahun}",
                    'deskripsi' => 'Perhitungan prioritas pembangunan antar dusun menggunakan metode ELECTRE.',
                    'status' => ElectreCalculation::STATUS_SELESAI,
                    'versi' => $versi,
                    'is_latest' => true,
                    'total_alternatif' => $dusuns->count(),
                    'total_kriteria' => $kriterias->count(),
                    'calculated_by' => $userId,
                    'calculated_at' => now(),
                    'notes' => 'Semua kriteria diperlakukan sebagai benefit sesuai skala prioritas 1 sampai 5.',
                ]);

                foreach ($ranking as $item) {
                    ElectreResult::create([
                        'electre_calculation_id' => $calculation->id,
                        'dusun_id' => $item['dusun_id'],
                        'ranking' => $item['ranking'],
                        'skor_dominasi' => $item['skor_dominasi'],
                        'status_prioritas' => $item['status_prioritas'],
                        'keterangan' => $item['keterangan'],
                    ]);
                }

                $this->storeDetails($calculation, [
                    'matriks_keputusan' => $this->readableCriteriaMatrix($decisionMatrix, $dusuns, $kriterias),
                    'normalisasi' => [
                        'denominator' => $this->readableCriteriaVector($normalization['denominator'], $kriterias),
                        'matrix' => $this->readableCriteriaMatrix($normalization['matrix'], $dusuns, $kriterias),
                    ],
                    'pembobotan' => [
                        'weights' => $this->readableWeights($kriterias),
                        'matrix' => $this->readableCriteriaMatrix($weightedMatrix, $dusuns, $kriterias),
                    ],
                    'concordance_sets' => $sets['concordance'],
                    'discordance_sets' => $sets['discordance'],
                    'concordance_matrix' => $this->readablePairMatrix($concordanceMatrix, $dusuns),
                    'discordance_matrix' => $this->readablePairMatrix($discordanceMatrix, $dusuns),
                    'threshold' => [
                        'concordance' => $this->roundValue($thresholds['concordance']),
                        'discordance' => $this->roundValue($thresholds['discordance']),
                    ],
                    'dominant_concordance' => $this->readablePairMatrix($dominantMatrices['concordance'], $dusuns, false),
                    'dominant_discordance' => $this->readablePairMatrix($dominantMatrices['discordance'], $dusuns, false),
                    'aggregate_dominance' => $this->readablePairMatrix($aggregateDominance, $dusuns, false),
                    'ranking_summary' => $ranking,
                ]);

                TahunPerencanaan::where('tahun', $tahun)->update([
                    'perlu_hitung_ulang' => false,
                    'alasan_hitung_ulang' => null,
                    'last_electre_calculation_id' => $calculation->id,
                ]);

                return $calculation->load(['results.dusun', 'details', 'calculator']);
            });
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('Terjadi kesalahan saat menghitung ELECTRE. Kode Error: ELECTRE_CALCULATION_FAILED', 0, $e);
        }
    }

    private function getActiveDusuns(): Collection
    {
        return Dusun::aktif()
            ->orderBy('kode_alternatif')
            ->orderBy('nama_dusun')
            ->get();
    }

    private function getActiveKriterias(): Collection
    {
        return Kriteria::aktif()->ordered()->get();
    }

    private function validateInputs(int $tahun, Collection $dusuns, Collection $kriterias): void
    {
        if ($dusuns->count() < 2) {
            throw new RuntimeException('Minimal harus terdapat dua dusun aktif. Kode Error: ELECTRE_NO_ACTIVE_DUSUN');
        }

        if ($kriterias->isEmpty()) {
            throw new RuntimeException('Belum ada kriteria aktif. Kode Error: ELECTRE_NO_ACTIVE_KRITERIA');
        }

        $totalBobot = (float) $kriterias->sum('bobot');

        if (abs($totalBobot - 100.0) > 0.01) {
            throw new RuntimeException('Total bobot kriteria aktif harus 100%. Kode Error: ELECTRE_INVALID_WEIGHT_TOTAL');
        }

        $totalSeharusnya = $dusuns->count() * $kriterias->count();
        $totalTerisi = PenilaianAlternatif::tahun($tahun)
            ->whereIn('dusun_id', $dusuns->pluck('id'))
            ->whereIn('kriteria_id', $kriterias->pluck('id'))
            ->whereBetween('nilai', [PenilaianAlternatif::NILAI_MIN, PenilaianAlternatif::NILAI_MAX])
            ->count();

        if ($totalTerisi !== $totalSeharusnya) {
            throw new RuntimeException('Penilaian alternatif belum lengkap. Kode Error: ELECTRE_INCOMPLETE_ASSESSMENT');
        }
    }

    private function buildDecisionMatrix(int $tahun, Collection $dusuns, Collection $kriterias): array
    {
        $penilaians = PenilaianAlternatif::tahun($tahun)
            ->whereIn('dusun_id', $dusuns->pluck('id'))
            ->whereIn('kriteria_id', $kriterias->pluck('id'))
            ->get()
            ->keyBy(fn (PenilaianAlternatif $penilaian): string => "{$penilaian->dusun_id}:{$penilaian->kriteria_id}");

        $matrix = [];

        foreach ($dusuns as $dusun) {
            foreach ($kriterias as $kriteria) {
                $key = "{$dusun->id}:{$kriteria->id}";
                $matrix[$dusun->id][$kriteria->id] = (int) $penilaians[$key]->nilai;
            }
        }

        return $matrix;
    }

    private function normalizeMatrix(array $decisionMatrix, Collection $kriterias): array
    {
        $denominator = [];
        $matrix = [];

        foreach ($kriterias as $kriteria) {
            $sumSquares = 0.0;

            foreach ($decisionMatrix as $row) {
                $sumSquares += ((float) $row[$kriteria->id]) ** 2;
            }

            $denominator[$kriteria->id] = sqrt($sumSquares);
        }

        foreach ($decisionMatrix as $dusunId => $row) {
            foreach ($kriterias as $kriteria) {
                $matrix[$dusunId][$kriteria->id] = $denominator[$kriteria->id] > 0
                    ? (float) $row[$kriteria->id] / $denominator[$kriteria->id]
                    : 0.0;
            }
        }

        return [
            'denominator' => $denominator,
            'matrix' => $matrix,
        ];
    }

    private function buildWeightedMatrix(array $normalizationMatrix, Collection $kriterias): array
    {
        $matrix = [];

        foreach ($normalizationMatrix as $dusunId => $row) {
            foreach ($kriterias as $kriteria) {
                $weight = (float) $kriteria->bobot / 100;
                $matrix[$dusunId][$kriteria->id] = (float) $row[$kriteria->id] * $weight;
            }
        }

        return $matrix;
    }

    private function buildConcordanceDiscordanceSets(array $weightedMatrix, Collection $dusuns, Collection $kriterias): array
    {
        $concordance = [];
        $discordance = [];
        $concordanceIds = [];
        $discordanceIds = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                if ($dusunK->id === $dusunL->id) {
                    $concordance[$dusunK->kode_alternatif][$dusunL->kode_alternatif] = [];
                    $discordance[$dusunK->kode_alternatif][$dusunL->kode_alternatif] = [];
                    $concordanceIds[$dusunK->id][$dusunL->id] = [];
                    $discordanceIds[$dusunK->id][$dusunL->id] = [];
                    continue;
                }

                foreach ($kriterias as $kriteria) {
                    if ($weightedMatrix[$dusunK->id][$kriteria->id] >= $weightedMatrix[$dusunL->id][$kriteria->id]) {
                        $concordance[$dusunK->kode_alternatif][$dusunL->kode_alternatif][] = $kriteria->kode;
                        $concordanceIds[$dusunK->id][$dusunL->id][] = $kriteria->id;
                    } else {
                        $discordance[$dusunK->kode_alternatif][$dusunL->kode_alternatif][] = $kriteria->kode;
                        $discordanceIds[$dusunK->id][$dusunL->id][] = $kriteria->id;
                    }
                }
            }
        }

        return [
            'concordance' => $concordance,
            'discordance' => $discordance,
            'concordance_ids' => $concordanceIds,
            'discordance_ids' => $discordanceIds,
        ];
    }

    private function buildConcordanceMatrix(array $concordanceSets, Collection $dusuns, Collection $kriterias): array
    {
        $weights = $kriterias->mapWithKeys(fn (Kriteria $kriteria): array => [
            $kriteria->id => (float) $kriteria->bobot / 100,
        ]);
        $matrix = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                if ($dusunK->id === $dusunL->id) {
                    $matrix[$dusunK->id][$dusunL->id] = 0.0;
                    continue;
                }

                $matrix[$dusunK->id][$dusunL->id] = array_reduce(
                    $concordanceSets[$dusunK->id][$dusunL->id] ?? [],
                    fn (float $sum, int $kriteriaId): float => $sum + (float) $weights[$kriteriaId],
                    0.0,
                );
            }
        }

        return $matrix;
    }

    private function buildDiscordanceMatrix(array $discordanceSets, array $weightedMatrix, Collection $dusuns, Collection $kriterias): array
    {
        $matrix = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                if ($dusunK->id === $dusunL->id) {
                    $matrix[$dusunK->id][$dusunL->id] = 0.0;
                    continue;
                }

                $allDiffs = [];
                $discordanceDiffs = [];

                foreach ($kriterias as $kriteria) {
                    $diff = abs($weightedMatrix[$dusunK->id][$kriteria->id] - $weightedMatrix[$dusunL->id][$kriteria->id]);
                    $allDiffs[] = $diff;

                    if (in_array($kriteria->id, $discordanceSets[$dusunK->id][$dusunL->id] ?? [], true)) {
                        $discordanceDiffs[] = $diff;
                    }
                }

                $denominator = max($allDiffs);
                $matrix[$dusunK->id][$dusunL->id] = empty($discordanceDiffs) || $denominator == 0.0
                    ? 0.0
                    : max($discordanceDiffs) / $denominator;
            }
        }

        return $matrix;
    }

    private function calculateThresholds(array $concordanceMatrix, array $discordanceMatrix, int $alternativeCount): array
    {
        $divider = $alternativeCount * ($alternativeCount - 1);
        $concordanceTotal = 0.0;
        $discordanceTotal = 0.0;

        foreach ($concordanceMatrix as $rowId => $row) {
            foreach ($row as $columnId => $value) {
                if ((int) $rowId !== (int) $columnId) {
                    $concordanceTotal += (float) $value;
                    $discordanceTotal += (float) $discordanceMatrix[$rowId][$columnId];
                }
            }
        }

        return [
            'concordance' => $divider > 0 ? $concordanceTotal / $divider : 0.0,
            'discordance' => $divider > 0 ? $discordanceTotal / $divider : 0.0,
        ];
    }

    private function buildDominantMatrices(array $concordanceMatrix, array $discordanceMatrix, array $thresholds, Collection $dusuns): array
    {
        $dominantConcordance = [];
        $dominantDiscordance = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                if ($dusunK->id === $dusunL->id) {
                    $dominantConcordance[$dusunK->id][$dusunL->id] = 0;
                    $dominantDiscordance[$dusunK->id][$dusunL->id] = 0;
                    continue;
                }

                $dominantConcordance[$dusunK->id][$dusunL->id] = $concordanceMatrix[$dusunK->id][$dusunL->id] >= $thresholds['concordance'] ? 1 : 0;
                $dominantDiscordance[$dusunK->id][$dusunL->id] = $discordanceMatrix[$dusunK->id][$dusunL->id] <= $thresholds['discordance'] ? 1 : 0;
            }
        }

        return [
            'concordance' => $dominantConcordance,
            'discordance' => $dominantDiscordance,
        ];
    }

    private function buildAggregateDominanceMatrix(array $dominantConcordance, array $dominantDiscordance, Collection $dusuns): array
    {
        $matrix = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                $matrix[$dusunK->id][$dusunL->id] = $dusunK->id === $dusunL->id
                    ? 0
                    : $dominantConcordance[$dusunK->id][$dusunL->id] * $dominantDiscordance[$dusunK->id][$dusunL->id];
            }
        }

        return $matrix;
    }

    private function buildRanking(array $aggregateDominance, array $weightedMatrix, Collection $dusuns): array
    {
        $items = [];

        foreach ($dusuns as $dusun) {
            $items[] = [
                'dusun_id' => $dusun->id,
                'kode_alternatif' => $dusun->kode_alternatif,
                'nama_dusun' => $dusun->nama_dusun,
                'skor_dominasi' => array_sum($aggregateDominance[$dusun->id]),
                'total_terbobot' => array_sum($weightedMatrix[$dusun->id]),
            ];
        }

        usort($items, function (array $left, array $right): int {
            return $right['skor_dominasi'] <=> $left['skor_dominasi']
                ?: $right['total_terbobot'] <=> $left['total_terbobot']
                ?: strcmp($left['nama_dusun'], $right['nama_dusun']);
        });

        foreach ($items as $index => &$item) {
            $ranking = $index + 1;
            $item['ranking'] = $ranking;
            $item['total_terbobot'] = $this->roundValue($item['total_terbobot']);
            $item['status_prioritas'] = $this->statusPrioritas($ranking);
            $item['keterangan'] = "Ranking {$ranking} dengan skor dominasi {$item['skor_dominasi']}.";
        }

        return $items;
    }

    private function generateCalculationCode(int $tahun): string
    {
        return 'ELC-'.$tahun.'-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function statusPrioritas(int $ranking): string
    {
        return match ($ranking) {
            1 => 'Prioritas Utama',
            2 => 'Prioritas Kedua',
            3 => 'Prioritas Ketiga',
            4 => 'Prioritas Keempat',
            default => "Prioritas ke-{$ranking}",
        };
    }

    private function storeDetails(ElectreCalculation $calculation, array $details): void
    {
        foreach ($details as $tahap => $data) {
            ElectreResultDetail::create([
                'electre_calculation_id' => $calculation->id,
                'tahap' => $tahap,
                'data' => $data,
            ]);
        }
    }

    private function readableCriteriaMatrix(array $matrix, Collection $dusuns, Collection $kriterias): array
    {
        $readable = [];

        foreach ($dusuns as $dusun) {
            foreach ($kriterias as $kriteria) {
                $readable[$dusun->kode_alternatif][$kriteria->kode] = $this->roundValue((float) $matrix[$dusun->id][$kriteria->id]);
            }
        }

        return $readable;
    }

    private function readableCriteriaVector(array $values, Collection $kriterias): array
    {
        $readable = [];

        foreach ($kriterias as $kriteria) {
            $readable[$kriteria->kode] = $this->roundValue((float) $values[$kriteria->id]);
        }

        return $readable;
    }

    private function readableWeights(Collection $kriterias): array
    {
        $weights = [];

        foreach ($kriterias as $kriteria) {
            $weights[$kriteria->kode] = $this->roundValue((float) $kriteria->bobot / 100);
        }

        return $weights;
    }

    private function readablePairMatrix(array $matrix, Collection $dusuns, bool $round = true): array
    {
        $readable = [];

        foreach ($dusuns as $rowDusun) {
            foreach ($dusuns as $columnDusun) {
                $value = $matrix[$rowDusun->id][$columnDusun->id] ?? 0;
                $readable[$rowDusun->kode_alternatif][$columnDusun->kode_alternatif] = $round
                    ? $this->roundValue((float) $value)
                    : (int) $value;
            }
        }

        return $readable;
    }

    private function roundValue(float $value): float
    {
        return round($value, 6);
    }
}
