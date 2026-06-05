<?php

namespace App\Http\Controllers\KepalaDusun;

use App\Http\Controllers\Controller;
use App\Models\UsulanPembangunan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $dusun = $user->dusun;
        $query = UsulanPembangunan::query();

        if ($user->dusun_id) {
            $query->where('dusun_id', $user->dusun_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return view('kepala-dusun.dashboard', [
            'dusun' => $dusun,
            'totalUsulan' => (clone $query)->count(),
            'totalDiajukan' => (clone $query)->where('status', UsulanPembangunan::STATUS_DIAJUKAN)->count(),
            'totalDiproses' => (clone $query)->where('status', UsulanPembangunan::STATUS_DIPROSES)->count(),
            'totalDiterima' => (clone $query)->where('status', UsulanPembangunan::STATUS_DITERIMA)->count(),
            'totalMasukPrioritas' => (clone $query)->where('status', UsulanPembangunan::STATUS_MASUK_PRIORITAS)->count(),
            'latestUsulans' => (clone $query)->latest()->take(5)->get(),
        ]);
    }
}
