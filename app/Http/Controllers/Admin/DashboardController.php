<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\UsulanPembangunan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalDusun' => Dusun::count(),
            'totalKriteria' => Kriteria::count(),
            'totalUsulan' => UsulanPembangunan::count(),
            'totalPerhitungan' => ElectreCalculation::count(),
        ]);
    }
}
