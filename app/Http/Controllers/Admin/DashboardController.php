<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use App\Models\User;
use App\Services\RekapUsulanService;
use App\Services\TahunAktifService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TahunAktifService $tahunAktifService, RekapUsulanService $rekapUsulanService): View
    {
        $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
        $dusunAktifIds = Dusun::aktif()->pluck('id');
        $kriteriaAktifIds = Kriteria::aktif()->pluck('id');
        $usulanQuery = UsulanPembangunan::tahun($tahun);
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
            'totalUsulan' => (clone $usulanQuery)->count(),
            'totalUsulanUmumDesa' => (clone $usulanQuery)->tipe(UsulanPembangunan::TIPE_UMUM_DESA)->count(),
            'totalUsulanDusun' => (clone $usulanQuery)->tipe(UsulanPembangunan::TIPE_DUSUN)->count(),
            'totalUsulanLintasDusun' => (clone $usulanQuery)->tipe(UsulanPembangunan::TIPE_LINTAS_DUSUN)->count(),
            'totalDiajukan' => (clone $usulanQuery)->diajukan()->count(),
            'totalDiproses' => (clone $usulanQuery)->diproses()->count(),
            'totalDiterima' => (clone $usulanQuery)->diterima()->count(),
            'totalDitolak' => (clone $usulanQuery)->ditolak()->count(),
            'totalMasukPrioritas' => (clone $usulanQuery)->masukPrioritas()->count(),
            'totalPerhitungan' => ElectreCalculation::tahun($tahun)->count(),
            'totalUser' => User::count(),
            'totalUserAktif' => User::active()->count(),
            'totalAdmin' => User::role(User::ROLE_ADMIN)->count(),
            'totalKepalaDesa' => User::role(User::ROLE_KEPALA_DESA)->count(),
            'totalKepalaDusun' => User::role(User::ROLE_KEPALA_DUSUN)->count(),
            'latestCalculation' => ElectreCalculation::tahun($tahun)->selesai()->latestVersion()->latest('calculated_at')->latest()->first(),
            'latestUsulan' => UsulanPembangunan::with('dusun')->tahun($tahun)->latest()->take(5)->get(),
            'tahunPenilaian' => $tahun,
            'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
            'rekapUsulan' => $rekapUsulanService->perDusun($tahun),
            'totalPenilaianSeharusnya' => $totalPenilaianSeharusnya,
            'totalPenilaianTerisi' => $totalPenilaianTerisi,
            'persentasePenilaian' => $totalPenilaianSeharusnya > 0 ? round(($totalPenilaianTerisi / $totalPenilaianSeharusnya) * 100, 2) : 0,
        ]);
    }
}
