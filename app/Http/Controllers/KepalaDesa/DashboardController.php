<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\TahunPerencanaan;
use App\Services\BudgetAllocationService;
use App\Services\TahunAktifService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TahunAktifService $tahunAktifService, BudgetAllocationService $budgetService): View
    {
        $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
        $perhitunganTerakhir = ElectreCalculation::selesai()
            ->tahun($tahun)
            ->latestVersion()
            ->latest('calculated_at')
            ->latest()
            ->first();

        $periode = TahunPerencanaan::where('tahun', $tahun)->first();

        return view('kepala-desa.dashboard', [
            'tahun' => $tahun,
            'periode' => $periode,
            'budgetSummary' => $periode ? $budgetService->summary($periode) : ['pagu' => null, 'total_ditetapkan' => 0, 'sisa_pagu' => null, 'jumlah_program_ditetapkan' => 0],
            'totalSelesai' => ElectreCalculation::tahun($tahun)->selesai()->count(),
            'perhitunganTerakhir' => $perhitunganTerakhir,
            'totalDusunAktif' => Dusun::aktif()->count(),
            'prioritasUtamaTerbaru' => $perhitunganTerakhir
                ? ElectreResult::with('program.dusun')->where('electre_calculation_id', $perhitunganTerakhir->id)->where('ranking', 1)->first()
                : null,
        ]);
    }
}
