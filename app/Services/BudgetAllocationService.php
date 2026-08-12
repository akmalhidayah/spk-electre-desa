<?php

namespace App\Services;

use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\TahunPerencanaan;
use Illuminate\Support\Collection;

class BudgetAllocationService
{
    public function summary(TahunPerencanaan $periode): array
    {
        $decisions = KeputusanAkhir::with(['details', 'program'])
            ->where('tahun_perencanaan_id', $periode->id)
            ->where('status', KeputusanAkhir::STATUS_DITETAPKAN)
            ->get();

        $programs = collect();
        foreach ($decisions as $decision) {
            if ($decision->details->isNotEmpty()) {
                foreach ($decision->details as $detail) {
                    $programs->put($detail->usulan_pembangunan_id, (float) $detail->estimasi_anggaran_snapshot);
                }
            } elseif ($decision->usulan_pembangunan_id) {
                $programs->put($decision->usulan_pembangunan_id, (float) ($decision->program?->estimasi_anggaran ?? 0));
            }
        }

        $pagu = $periode->pagu_anggaran !== null ? (float) $periode->pagu_anggaran : null;
        $allocated = (float) $programs->sum();

        return [
            'pagu' => $pagu,
            'total_ditetapkan' => $allocated,
            'sisa_pagu' => $pagu === null ? null : max($pagu - $allocated, 0),
            'jumlah_program_ditetapkan' => $programs->count(),
            'program_ids_ditetapkan' => $programs->keys()->map(fn ($id) => (int) $id)->all(),
            'persentase_alokasi' => $pagu !== null && $pagu > 0 ? min(($allocated / $pagu) * 100, 100) : 0,
        ];
    }

    public function amountMap(ElectreCalculation $calculation): Collection
    {
        $calculation->loadMissing(['details', 'results.program']);
        $ranking = collect($calculation->details->firstWhere('tahap', 'ranking_summary')?->data ?? [])->keyBy('usulan_pembangunan_id');
        $metadata = collect($calculation->details->firstWhere('tahap', 'metadata_alternatif')?->data ?? [])->keyBy('id');

        return $calculation->results->mapWithKeys(function (ElectreResult $result) use ($ranking, $metadata): array {
            $amount = data_get($ranking->get($result->usulan_pembangunan_id), 'estimasi_anggaran');
            $amount ??= data_get($metadata->get($result->usulan_pembangunan_id), 'estimasi_anggaran');
            $amount ??= $result->program?->estimasi_anggaran;

            return [$result->id => $amount !== null ? (float) $amount : null];
        });
    }

    public function simulate(TahunPerencanaan $periode, ElectreCalculation $calculation): array
    {
        $summary = $this->summary($periode);
        $amounts = $this->amountMap($calculation);
        $determined = collect($summary['program_ids_ditetapkan']);
        $remaining = $summary['sisa_pagu'];
        $potential = 0;

        $results = $calculation->results->sortBy('ranking')->values()->each(function (ElectreResult $result) use ($amounts, $determined, &$remaining, &$potential): void {
            $amount = $amounts->get($result->id);
            $result->setAttribute('estimasi_anggaran_snapshot', $amount);

            if ($determined->contains((int) $result->usulan_pembangunan_id)) {
                [$status, $label] = ['ditetapkan', 'Sudah Ditetapkan'];
            } elseif ($amount === null) {
                [$status, $label] = ['anggaran_belum_diisi', 'Anggaran Belum Diisi'];
            } elseif ($remaining === null) {
                [$status, $label] = ['pagu_belum_diatur', 'Pagu Belum Diatur'];
            } elseif ($amount <= $remaining) {
                [$status, $label] = ['terakomodasi', 'Terakomodasi Anggaran'];
                $remaining -= $amount;
                $potential++;
            } else {
                [$status, $label] = ['belum_terakomodasi', 'Belum Terakomodasi'];
            }

            $result->setAttribute('status_anggaran', $status);
            $result->setAttribute('label_anggaran', $label);
        });

        $summary['potensi_terakomodasi'] = $potential;
        $summary['sisa_simulasi'] = $remaining;

        return ['summary' => $summary, 'results' => $results];
    }

    public function isOfficialCalculation(ElectreCalculation $calculation): bool
    {
        $calculation->loadMissing('tahunPerencanaan');

        return $calculation->status === ElectreCalculation::STATUS_SELESAI
            && $calculation->is_latest
            && ! $calculation->tahunPerencanaan?->perlu_hitung_ulang
            && $calculation->isRegular();
    }
}
