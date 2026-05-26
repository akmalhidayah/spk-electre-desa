<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
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
        ]);
    }
}
