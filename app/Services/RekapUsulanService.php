<?php

namespace App\Services;

use App\Models\Dusun;
use App\Models\UsulanPembangunan;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class RekapUsulanService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function perDusun(int $tahun, ?EloquentCollection $dusuns = null): Collection
    {
        $dusuns ??= Dusun::aktif()
            ->orderBy('kode_alternatif')
            ->orderBy('nama_dusun')
            ->get();

        $usulans = UsulanPembangunan::with('dusunsTerkait')
            ->tahun($tahun)
            ->get();

        return $dusuns->map(function (Dusun $dusun) use ($usulans): array {
            $items = $usulans->filter(function (UsulanPembangunan $usulan) use ($dusun): bool {
                if ((int) $usulan->dusun_id === (int) $dusun->id) {
                    return true;
                }

                return $usulan->dusunsTerkait->contains('id', $dusun->id);
            });

            $diterima = $items->where('status', UsulanPembangunan::STATUS_DITERIMA);

            return [
                'dusun' => $dusun,
                'jumlah_usulan' => $items->count(),
                'jumlah_usulan_diterima' => $diterima->count(),
                'jumlah_usulan_infrastruktur' => $this->countKategori($items, ['infrastruktur']),
                'jumlah_usulan_jalan_jembatan_talud' => $this->countKategori($items, ['jalan', 'jembatan', 'talud']),
                'jumlah_usulan_air_bersih_irigasi_drainase' => $this->countKategori($items, ['air bersih', 'irigasi', 'drainase']),
                'total_penerima_manfaat_lk' => (int) $items->sum('penerima_manfaat_lk'),
                'total_penerima_manfaat_pr' => (int) $items->sum('penerima_manfaat_pr'),
                'total_penerima_manfaat' => (int) $items->sum(fn (UsulanPembangunan $item): int => $item->total_penerima_manfaat),
                'total_volume_jalan_meter' => $this->sumVolumeMeter($items, ['jalan', 'jembatan', 'talud']),
                'total_volume_air_irigasi_meter' => $this->sumVolumeMeter($items, ['air bersih', 'irigasi', 'drainase']),
                'kegiatan_utama' => $diterima->take(5)->pluck('nama_kegiatan')->values(),
            ];
        });
    }

    private function countKategori(Collection $items, array $keywords): int
    {
        return $items->filter(fn (UsulanPembangunan $item): bool => $this->matchesKategori($item, $keywords))->count();
    }

    private function sumVolumeMeter(Collection $items, array $keywords): float
    {
        return (float) $items
            ->filter(fn (UsulanPembangunan $item): bool => $this->matchesKategori($item, $keywords) && strtolower((string) $item->satuan) === 'meter')
            ->sum('prakiraan_volume');
    }

    private function matchesKategori(UsulanPembangunan $item, array $keywords): bool
    {
        $haystack = strtolower((string) $item->kategori_kegiatan.' '.$item->nama_kegiatan);

        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
