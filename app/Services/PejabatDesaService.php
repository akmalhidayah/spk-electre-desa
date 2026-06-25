<?php

namespace App\Services;

use App\Models\Dusun;
use App\Models\StrukturOrganisasiDesa;
use App\Models\WelcomeDesaSetting;
use Illuminate\Support\Facades\Schema;

class PejabatDesaService
{
    public function kepalaDesaName(): ?string
    {
        return $this->strukturQuery()
            ?->where('jabatan', 'Kepala Desa')
            ->value('nama');
    }

    public function kepalaDusunName(Dusun $dusun): ?string
    {
        $namaDusun = trim(str_replace('Dusun ', '', $dusun->nama_dusun));

        return $this->strukturQuery()
            ?->where(function ($query) use ($dusun, $namaDusun): void {
                $query->where('jabatan', 'Kepala Dusun '.$namaDusun)
                    ->orWhere('jabatan', 'Kepala '.$dusun->nama_dusun)
                    ->orWhere('jabatan', 'like', '%'.$namaDusun.'%');
            })
            ->orderBy('urutan')
            ->value('nama');
    }

    private function strukturQuery()
    {
        if (! Schema::hasTable('welcome_desa_settings') || ! Schema::hasTable('struktur_organisasi_desas')) {
            return null;
        }

        $setting = WelcomeDesaSetting::query()->aktif()->latest('id')->first()
            ?? WelcomeDesaSetting::query()->latest('id')->first();

        return StrukturOrganisasiDesa::query()
            ->when($setting, fn ($query) => $query->where('welcome_desa_setting_id', $setting->id))
            ->aktif()
            ->orderBy('urutan');
    }
}
