<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTahunPerencanaanRequest;
use App\Http\Requests\UpdateTahunPerencanaanRequest;
use App\Models\TahunPerencanaan;
use App\Services\BudgetAllocationService;
use App\Services\TahunAktifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TahunPerencanaanController extends Controller
{
    public function index(): View
    {
        return view('admin.tahun-perencanaan.index', [
            'periodes' => TahunPerencanaan::with('lastElectreCalculation')
                ->orderByDesc('tahun')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.tahun-perencanaan.create', [
            'periode' => new TahunPerencanaan(['tahun' => now()->year]),
        ]);
    }

    public function store(StoreTahunPerencanaanRequest $request, TahunAktifService $tahunAktifService): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');
        $data['is_active'] = false;
        $data['is_locked'] = $request->boolean('is_locked');

        $periode = TahunPerencanaan::create($data);

        if ($isActive) {
            $tahunAktifService->setActiveYear((int) $periode->tahun);
        }

        return redirect()
            ->route('admin.tahun-perencanaan.index')
            ->with('success', 'Tahun perencanaan berhasil ditambahkan.');
    }

    public function edit(TahunPerencanaan $tahunPerencanaan): View
    {
        return view('admin.tahun-perencanaan.edit', [
            'periode' => $tahunPerencanaan,
        ]);
    }

    public function update(UpdateTahunPerencanaanRequest $request, TahunPerencanaan $tahunPerencanaan, TahunAktifService $tahunAktifService, BudgetAllocationService $budgetService): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');
        $data['is_active'] = false;
        $data['is_locked'] = $request->boolean('is_locked');
        DB::transaction(function () use ($tahunPerencanaan, $data, $isActive, $tahunAktifService, $budgetService): void {
            $lockedPeriode = TahunPerencanaan::whereKey($tahunPerencanaan->id)->lockForUpdate()->firstOrFail();
            $allocated = $budgetService->summary($lockedPeriode)['total_ditetapkan'];

            if ((float) $data['pagu_anggaran'] < $allocated) {
                throw ValidationException::withMessages([
                    'pagu_anggaran' => 'Pagu anggaran tidak dapat lebih kecil dari total anggaran program yang telah ditetapkan sebesar Rp'.number_format($allocated, 0, ',', '.').'.',
                ]);
            }

            $lockedPeriode->update($data);

            if ($isActive) {
                $tahunAktifService->setActiveYear((int) $lockedPeriode->tahun);
            }
        });

        return redirect()
            ->route('admin.tahun-perencanaan.index')
            ->with('success', 'Tahun perencanaan berhasil diperbarui.');
    }

    public function setActive(TahunPerencanaan $tahunPerencanaan, TahunAktifService $tahunAktifService): RedirectResponse
    {
        $tahunAktifService->setActiveYear((int) $tahunPerencanaan->tahun);

        return back()->with('success', "Tahun {$tahunPerencanaan->tahun} berhasil dijadikan tahun aktif.");
    }

    public function toggleLock(Request $request, TahunPerencanaan $tahunPerencanaan): RedirectResponse
    {
        $tahunPerencanaan->update(['is_locked' => ! $tahunPerencanaan->is_locked]);

        return back()->with('success', 'Status kunci periode berhasil diperbarui.');
    }
}
