<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use App\Services\BudgetAllocationService;
use App\Services\RekapUsulanService;
use App\Services\TahunAktifService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TahunAktifService $tahunAktifService, RekapUsulanService $rekapUsulanService, BudgetAllocationService $budgetService): View
    {
        $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
        $dusunAktifIds = Dusun::aktif()->pluck('id');
        $periode = TahunPerencanaan::where('tahun', $tahun)->first();
        $kriteriaAktifIds = Kriteria::aktif()->pluck('id');
        $usulanQuery = UsulanPembangunan::tahun($tahun);
        $programIds = (clone $usulanQuery)->diterima()->pluck('id');
        $totalPenilaianSeharusnya = $programIds->count() * $kriteriaAktifIds->count();
        $totalPenilaianTerisi = $periode ? PenilaianAlternatif::periode($periode->id)
            ->whereIn('usulan_pembangunan_id', $programIds)
            ->whereIn('kriteria_id', $kriteriaAktifIds)
            ->count() : 0;

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
            'totalMasukPrioritas' => 0,
            'totalPerhitungan' => ElectreCalculation::tahun($tahun)->count(),
            'totalUser' => User::count(),
            'totalUserAktif' => User::active()->count(),
            'totalAdmin' => User::role(User::ROLE_ADMIN)->count(),
            'totalKepalaDesa' => User::role(User::ROLE_KEPALA_DESA)->count(),
            'totalKepalaDusun' => User::role(User::ROLE_KEPALA_DUSUN)->count(),
            'latestCalculation' => ElectreCalculation::tahun($tahun)->selesai()->latestVersion()->latest('calculated_at')->latest()->first(),
            'latestUsulan' => UsulanPembangunan::with('dusun')->tahun($tahun)->latest()->take(5)->get(),
            'tahunPenilaian' => $tahun,
            'periode' => $periode,
            'budgetSummary' => $periode ? $budgetService->summary($periode) : ['pagu' => null, 'total_ditetapkan' => 0, 'sisa_pagu' => null, 'jumlah_program_ditetapkan' => 0],
            'rekapUsulan' => $rekapUsulanService->perDusun($tahun),
            'totalPenilaianSeharusnya' => $totalPenilaianSeharusnya,
            'totalPenilaianTerisi' => $totalPenilaianTerisi,
            'persentasePenilaian' => $totalPenilaianSeharusnya > 0 ? round(($totalPenilaianTerisi / $totalPenilaianSeharusnya) * 100, 2) : 0,
        ]);
    }
}
