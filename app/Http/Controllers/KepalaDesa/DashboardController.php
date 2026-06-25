<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\TahunPerencanaan;
use App\Services\TahunAktifService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TahunAktifService $tahunAktifService): View
    {
        $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
        $perhitunganTerakhir = ElectreCalculation::selesai()
            ->tahun($tahun)
            ->latestVersion()
            ->latest('calculated_at')
            ->latest()
            ->first();

        return view('kepala-desa.dashboard', [
            'tahun' => $tahun,
            'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
            'totalSelesai' => ElectreCalculation::tahun($tahun)->selesai()->count(),
            'perhitunganTerakhir' => $perhitunganTerakhir,
            'totalDusunAktif' => Dusun::aktif()->count(),
            'prioritasUtamaTerbaru' => $perhitunganTerakhir
                ? ElectreResult::with('dusun')->where('electre_calculation_id', $perhitunganTerakhir->id)->where('ranking', 1)->first()
                : null,
        ]);
    }
}
