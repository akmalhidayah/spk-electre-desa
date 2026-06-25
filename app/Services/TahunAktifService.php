<?php

namespace App\Services;

use App\Models\TahunPerencanaan;
use Illuminate\Support\Facades\DB;

class TahunAktifService
{
    public function getActiveYear(): int
    {
        return TahunPerencanaan::active()->value('tahun') ?? (int) date('Y');
    }

    public function resolveYear(?int $tahun = null): int
    {
        return $tahun ?: $this->getActiveYear();
    }

    public function getActivePeriod(): ?TahunPerencanaan
    {
        return TahunPerencanaan::active()->first();
    }

    public function setActiveYear(int $tahun): TahunPerencanaan
    {
        return DB::transaction(function () use ($tahun): TahunPerencanaan {
            TahunPerencanaan::query()->update(['is_active' => false]);

            $periode = TahunPerencanaan::firstOrCreate(
                ['tahun' => $tahun],
                ['nama_periode' => "RKP Desa Barambang Tahun {$tahun}"],
            );

            $periode->update(['is_active' => true]);

            return $periode->refresh();
        });
    }
}
