<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $perhitunganTerakhir = ElectreCalculation::selesai()
            ->latest('calculated_at')
            ->latest()
            ->first();

        return view('kepala-desa.dashboard', [
            'totalSelesai' => ElectreCalculation::selesai()->count(),
            'perhitunganTerakhir' => $perhitunganTerakhir,
            'totalDusunAktif' => Dusun::aktif()->count(),
            'prioritasUtamaTerbaru' => $perhitunganTerakhir
                ? ElectreResult::with('dusun')->where('electre_calculation_id', $perhitunganTerakhir->id)->where('ranking', 1)->first()
                : null,
        ]);
    }
}
