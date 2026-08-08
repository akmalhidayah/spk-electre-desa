<?php

namespace App\Services;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\ElectreResultDetail;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ElectreService
{
    public function calculate(int $tahun, ?int $userId = null): ElectreCalculation
    {
        try {
            return DB::transaction(function () use ($tahun, $userId): ElectreCalculation {
                $periode = TahunPerencanaan::where('tahun', $tahun)->lockForUpdate()->firstOrFail();
                $dusuns = $this->getAcceptedPrograms($periode->id);
                $codes = $dusuns->values()->mapWithKeys(fn (UsulanPembangunan $program, int $index) => [$program->id => 'A'.($index + 1)])->all();
                $kriterias = $this->getActiveKriterias();
                $this->validateInputs($periode, $dusuns, $kriterias);

                $decisionMatrix = $this->buildDecisionMatrix($tahun, $dusuns, $kriterias);
                $normalization = $this->normalizeMatrix($decisionMatrix, $kriterias);
                $weightedMatrix = $this->buildWeightedMatrix($normalization['matrix'], $kriterias);
                $sets = $this->buildConcordanceDiscordanceSets($weightedMatrix, $dusuns, $kriterias, $codes);
                $concordanceMatrix = $this->buildConcordanceMatrix($sets['concordance_ids'], $dusuns, $kriterias);
                $discordanceMatrix = $this->buildDiscordanceMatrix($sets['discordance_ids'], $weightedMatrix, $dusuns, $kriterias);
                $thresholds = $this->calculateThresholds($concordanceMatrix, $discordanceMatrix, $dusuns->count());
                $dominantMatrices = $this->buildDominantMatrices($concordanceMatrix, $discordanceMatrix, $thresholds, $dusuns);
                $aggregateDominance = $this->buildAggregateDominanceMatrix($dominantMatrices['concordance'], $dominantMatrices['discordance'], $dusuns);
                $ranking = $this->buildRanking($aggregateDominance, $weightedMatrix, $dusuns, $kriterias, $codes);

                ElectreCalculation::periode($periode->id)->lockForUpdate()->get(['id']);
                $versi = ((int) ElectreCalculation::periode($periode->id)->max('versi')) + 1;

                ElectreCalculation::periode($periode->id)->update(['is_latest' => false]);

                $calculation = ElectreCalculation::create([
                    'tahun_perencanaan_id' => $periode->id,
                    'kode_perhitungan' => $this->generateCalculationCode($tahun),
                    'judul' => "Perhitungan Pembangunan Usulan Tahun {$tahun}",
                    'deskripsi' => 'Perhitungan prioritas program pembangunan menggunakan metode ELECTRE.',
                    'status' => ElectreCalculation::STATUS_SELESAI,
                    'versi' => $versi,
                    'is_latest' => true,
                    'total_alternatif' => $dusuns->count(),
                    'total_kriteria' => $kriterias->count(),
                    'calculated_by' => $userId,
                    'calculated_at' => now(),
                    'notes' => 'Perhitungan mendukung kriteria benefit dan cost sesuai pengaturan dasar penilaian.',
                ]);

                foreach ($ranking as $item) {
                    ElectreResult::create([
                        'electre_calculation_id' => $calculation->id,
                        'usulan_pembangunan_id' => $item['usulan_pembangunan_id'],
                        'kode_alternatif' => $item['kode_alternatif'],
                        'nama_program_snapshot' => $item['nama_program'],
                        'lokasi_snapshot' => $item['lokasi'],
                        'nama_dusun_snapshot' => $item['nama_dusun'],
                        'ranking' => $item['ranking'],
                        'skor_dominasi' => $item['skor_dominasi'],
                        'total_preferensi' => $item['total_preferensi'],
                        'status_prioritas' => $item['status_prioritas'],
                        'keterangan' => $item['keterangan'],
                    ]);
                }

                $this->storeDetails($calculation, [
                    'metadata_alternatif' => $dusuns->map(fn (UsulanPembangunan $program) => ['id' => $program->id, 'kode' => $codes[$program->id], 'nama_program' => $program->nama_kegiatan, 'lokasi' => $program->lokasi_label])->values()->all(),
                    'metadata_kriteria' => $kriterias->map(fn (Kriteria $kriteria) => ['id' => $kriteria->id, 'kode' => $kriteria->kode, 'nama' => $kriteria->nama_kriteria, 'bobot' => $kriteria->bobot, 'tipe' => $kriteria->tipe, 'skala' => $kriteria->skala_penilaian])->values()->all(),
                    'matriks_keputusan' => $this->readableCriteriaMatrix($decisionMatrix, $dusuns, $kriterias, $codes),
                    'normalisasi' => [
                        'denominator' => $this->readableCriteriaVector($normalization['denominator'], $kriterias),
                        'matrix' => $this->readableCriteriaMatrix($normalization['matrix'], $dusuns, $kriterias, $codes),
                    ],
                    'pembobotan' => [
                        'weights' => $this->readableWeights($kriterias),
                        'types' => $this->readableCriteriaTypes($kriterias),
                        'matrix' => $this->readableCriteriaMatrix($weightedMatrix, $dusuns, $kriterias, $codes),
                    ],
                    'concordance_sets' => $sets['concordance'],
                    'discordance_sets' => $sets['discordance'],
                    'concordance_matrix' => $this->readablePairMatrix($concordanceMatrix, $dusuns, $codes),
                    'discordance_matrix' => $this->readablePairMatrix($discordanceMatrix, $dusuns, $codes),
                    'threshold' => [
                        'concordance' => $this->roundValue($thresholds['concordance']),
                        'discordance' => $this->roundValue($thresholds['discordance']),
                    ],
                    'dominant_concordance' => $this->readablePairMatrix($dominantMatrices['concordance'], $dusuns, $codes, false),
                    'dominant_discordance' => $this->readablePairMatrix($dominantMatrices['discordance'], $dusuns, $codes, false),
                    'aggregate_dominance' => $this->readablePairMatrix($aggregateDominance, $dusuns, $codes, false),
                    'ranking_summary' => $ranking,
                ]);

                $periode->update([
                    'perlu_hitung_ulang' => false,
                    'alasan_hitung_ulang' => null,
                    'last_electre_calculation_id' => $calculation->id,
                ]);

                return $calculation->load(['results.program.dusun', 'results.program.dusunsTerkait', 'details', 'calculator', 'tahunPerencanaan']);
            });
        } catch (RuntimeException $e) {
            throw $e;
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'electre_calculation_tahun_versi_unique')) {
                throw new RuntimeException('Versi perhitungan untuk tahun ini bentrok karena ada proses lain. Silakan jalankan ulang perhitungan. Kode Error: ELECTRE_VERSION_CONFLICT', 0, $e);
            }

            throw new RuntimeException('Terjadi kesalahan saat menghitung ELECTRE. Kode Error: ELECTRE_CALCULATION_FAILED', 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Terjadi kesalahan saat menghitung ELECTRE. Kode Error: ELECTRE_CALCULATION_FAILED', 0, $e);
        }
    }

    private function getAcceptedPrograms(int $periodeId): Collection
    {
        return UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
            ->periode($periodeId)
            ->diterima()
            ->orderBy('id')
            ->get();
    }

    private function getActiveKriterias(): Collection
    {
        return Kriteria::aktif()->ordered()->get();
    }

    private function validateInputs(TahunPerencanaan $periode, Collection $dusuns, Collection $kriterias): void
    {
        if ($dusuns->count() < 2) {
            throw new RuntimeException('Minimal harus terdapat dua program diterima. Kode Error: ELECTRE_NO_ACCEPTED_PROGRAM');
        }

        if ($kriterias->isEmpty()) {
            throw new RuntimeException('Belum ada kriteria aktif. Kode Error: ELECTRE_NO_ACTIVE_KRITERIA');
        }

        $totalBobot = (float) $kriterias->sum('bobot');

        if (abs($totalBobot - 100.0) > 0.01) {
            throw new RuntimeException('Total bobot kriteria aktif harus 100%. Kode Error: ELECTRE_INVALID_WEIGHT_TOTAL');
        }

        $totalSeharusnya = $dusuns->count() * $kriterias->count();
        $totalTerisi = PenilaianAlternatif::periode($periode->id)
            ->whereIn('usulan_pembangunan_id', $dusuns->pluck('id'))
            ->whereIn('kriteria_id', $kriterias->pluck('id'))
            ->whereBetween('nilai', [PenilaianAlternatif::NILAI_MIN, PenilaianAlternatif::NILAI_MAX])
            ->count();

        if ($totalTerisi !== $totalSeharusnya) {
            throw new RuntimeException('Penilaian alternatif belum lengkap. Kode Error: ELECTRE_INCOMPLETE_ASSESSMENT');
        }
    }

    private function buildDecisionMatrix(int $tahun, Collection $dusuns, Collection $kriterias): array
    {
        $periodeId = TahunPerencanaan::where('tahun', $tahun)->value('id');
        $penilaians = PenilaianAlternatif::periode($periodeId)
            ->whereIn('usulan_pembangunan_id', $dusuns->pluck('id'))
            ->whereIn('kriteria_id', $kriterias->pluck('id'))
            ->get()
            ->keyBy(fn (PenilaianAlternatif $penilaian): string => "{$penilaian->usulan_pembangunan_id}:{$penilaian->kriteria_id}");

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

    private function buildConcordanceDiscordanceSets(array $weightedMatrix, Collection $dusuns, Collection $kriterias, array $codes): array
    {
        $concordance = [];
        $discordance = [];
        $concordanceIds = [];
        $discordanceIds = [];

        foreach ($dusuns as $dusunK) {
            foreach ($dusuns as $dusunL) {
                if ($dusunK->id === $dusunL->id) {
                    $concordance[$codes[$dusunK->id]][$codes[$dusunL->id]] = [];
                    $discordance[$codes[$dusunK->id]][$codes[$dusunL->id]] = [];
                    $concordanceIds[$dusunK->id][$dusunL->id] = [];
                    $discordanceIds[$dusunK->id][$dusunL->id] = [];

                    continue;
                }

                foreach ($kriterias as $kriteria) {
                    $isCost = ($kriteria->tipe ?: Kriteria::TIPE_BENEFIT) === Kriteria::TIPE_COST;
                    $betterOrEqual = $isCost
                        ? $weightedMatrix[$dusunK->id][$kriteria->id] <= $weightedMatrix[$dusunL->id][$kriteria->id]
                        : $weightedMatrix[$dusunK->id][$kriteria->id] >= $weightedMatrix[$dusunL->id][$kriteria->id];

                    if ($betterOrEqual) {
                        $concordance[$codes[$dusunK->id]][$codes[$dusunL->id]][] = $kriteria->kode;
                        $concordanceIds[$dusunK->id][$dusunL->id][] = $kriteria->id;
                    } else {
                        $discordance[$codes[$dusunK->id]][$codes[$dusunL->id]][] = $kriteria->kode;
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

    private function buildRanking(array $aggregateDominance, array $weightedMatrix, Collection $dusuns, Collection $kriterias, array $codes): array
    {
        $items = [];

        foreach ($dusuns as $dusun) {
            $items[] = [
                'usulan_pembangunan_id' => $dusun->id,
                'kode_alternatif' => $codes[$dusun->id],
                'nama_program' => $dusun->nama_kegiatan,
                'lokasi' => $dusun->lokasi_label,
                'nama_dusun' => $dusun->dusun?->nama_dusun,
                'skor_dominasi' => array_sum($aggregateDominance[$dusun->id]),
                'total_preferensi' => $this->totalPreferenceScore($weightedMatrix[$dusun->id], $kriterias),
                'tie_score' => $this->totalPreferenceScore($weightedMatrix[$dusun->id], $kriterias),
            ];
        }

        usort($items, function (array $left, array $right): int {
            return $right['skor_dominasi'] <=> $left['skor_dominasi']
                ?: $right['tie_score'] <=> $left['tie_score']
                ?: strcmp($left['nama_program'], $right['nama_program']);
        });

        foreach ($items as $index => &$item) {
            $ranking = $index + 1;
            $item['ranking'] = $ranking;
            $item['total_preferensi'] = $this->roundValue($item['total_preferensi']);
            unset($item['tie_score']);
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

    private function readableCriteriaMatrix(array $matrix, Collection $dusuns, Collection $kriterias, array $codes): array
    {
        $readable = [];

        foreach ($dusuns as $dusun) {
            foreach ($kriterias as $kriteria) {
                $readable[$codes[$dusun->id]][$kriteria->kode] = $this->roundValue((float) $matrix[$dusun->id][$kriteria->id]);
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

    private function readableCriteriaTypes(Collection $kriterias): array
    {
        $types = [];

        foreach ($kriterias as $kriteria) {
            $types[$kriteria->kode] = $kriteria->tipe ?: Kriteria::TIPE_BENEFIT;
        }

        return $types;
    }

    private function totalPreferenceScore(array $weightedRow, Collection $kriterias): float
    {
        $score = 0.0;

        foreach ($kriterias as $kriteria) {
            $value = (float) ($weightedRow[$kriteria->id] ?? 0);
            $score += ($kriteria->tipe ?: Kriteria::TIPE_BENEFIT) === Kriteria::TIPE_COST
                ? -$value
                : $value;
        }

        return $score;
    }

    private function readablePairMatrix(array $matrix, Collection $dusuns, array $codes, bool $round = true): array
    {
        $readable = [];

        foreach ($dusuns as $rowDusun) {
            foreach ($dusuns as $columnDusun) {
                $value = $matrix[$rowDusun->id][$columnDusun->id] ?? 0;
                $readable[$codes[$rowDusun->id]][$codes[$columnDusun->id]] = $round
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
