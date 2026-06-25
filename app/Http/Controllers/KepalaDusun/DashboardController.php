<?php

namespace App\Http\Controllers\KepalaDusun;

use App\Http\Controllers\Controller;
use App\Models\UsulanPembangunan;
use App\Services\TahunAktifService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TahunAktifService $tahunAktifService): View
    {
        $user = $request->user();
        $dusun = $user->dusun;
        $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
        $query = UsulanPembangunan::query();

        if ($user->dusun_id) {
            $query->tahun($tahun)
                ->where(function ($query) use ($user): void {
                    $query->where('dusun_id', $user->dusun_id)
                        ->orWhereHas('dusunsTerkait', fn ($query) => $query->where('dusuns.id', $user->dusun_id));
                });
        } else {
            $query->whereRaw('1 = 0');
        }

        return view('kepala-dusun.dashboard', [
            'dusun' => $dusun,
            'tahun' => $tahun,
            'totalUsulan' => (clone $query)->count(),
            'totalDiajukan' => (clone $query)->where('status', UsulanPembangunan::STATUS_DIAJUKAN)->count(),
            'totalDiproses' => (clone $query)->where('status', UsulanPembangunan::STATUS_DIPROSES)->count(),
            'totalDiterima' => (clone $query)->where('status', UsulanPembangunan::STATUS_DITERIMA)->count(),
            'totalDitolak' => (clone $query)->where('status', UsulanPembangunan::STATUS_DITOLAK)->count(),
            'totalMasukPrioritas' => (clone $query)->where('status', UsulanPembangunan::STATUS_MASUK_PRIORITAS)->count(),
            'latestUsulans' => (clone $query)->latest()->take(5)->get(),
        ]);
    }
}
