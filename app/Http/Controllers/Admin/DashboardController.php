<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\UsulanPembangunan;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $tahun = (int) date('Y');
        $dusunAktifIds = Dusun::aktif()->pluck('id');
        $kriteriaAktifIds = Kriteria::aktif()->pluck('id');
        $totalPenilaianSeharusnya = $dusunAktifIds->count() * $kriteriaAktifIds->count();
        $totalPenilaianTerisi = PenilaianAlternatif::tahun($tahun)
            ->whereIn('dusun_id', $dusunAktifIds)
            ->whereIn('kriteria_id', $kriteriaAktifIds)
            ->count();

        return view('admin.dashboard', [
            'totalDusun' => Dusun::count(),
            'totalDusunAktif' => $dusunAktifIds->count(),
            'totalKriteria' => Kriteria::count(),
            'totalKriteriaAktif' => $kriteriaAktifIds->count(),
            'totalUsulan' => UsulanPembangunan::count(),
            'totalPerhitungan' => ElectreCalculation::count(),
            'totalUser' => User::count(),
            'totalUserAktif' => User::active()->count(),
            'totalAdmin' => User::role(User::ROLE_ADMIN)->count(),
            'totalKepalaDesa' => User::role(User::ROLE_KEPALA_DESA)->count(),
            'totalKepalaDusun' => User::role(User::ROLE_KEPALA_DUSUN)->count(),
            'latestCalculation' => ElectreCalculation::selesai()->latest('calculated_at')->latest()->first(),
            'latestUsulan' => UsulanPembangunan::with('dusun')->latest()->take(5)->get(),
            'tahunPenilaian' => $tahun,
            'totalPenilaianSeharusnya' => $totalPenilaianSeharusnya,
            'totalPenilaianTerisi' => $totalPenilaianTerisi,
            'persentasePenilaian' => $totalPenilaianSeharusnya > 0 ? round(($totalPenilaianTerisi / $totalPenilaianSeharusnya) * 100, 2) : 0,
        ]);
    }
}
