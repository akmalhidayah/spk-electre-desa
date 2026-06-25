<?php

namespace App\Services;

use App\Models\TahunPerencanaan;

class RecalculationFlagService
{
    public function mark(int $tahun, string $reason): TahunPerencanaan
    {
        $periode = TahunPerencanaan::firstOrCreate(
            ['tahun' => $tahun],
            ['nama_periode' => "RKP Desa Barambang Tahun {$tahun}"],
        );

        $periode->forceFill([
            'perlu_hitung_ulang' => true,
            'alasan_hitung_ulang' => $reason,
            'last_data_changed_at' => now(),
        ])->save();

        return $periode;
    }

    public function clear(int $tahun, ?int $calculationId = null): void
    {
        TahunPerencanaan::where('tahun', $tahun)->update([
            'perlu_hitung_ulang' => false,
            'alasan_hitung_ulang' => null,
            'last_electre_calculation_id' => $calculationId,
        ]);
    }
}
