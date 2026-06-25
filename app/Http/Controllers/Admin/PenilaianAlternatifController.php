<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Services\RecalculationFlagService;
use App\Services\RekapUsulanService;
use App\Services\TahunAktifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PenilaianAlternatifController extends Controller
{
    public function index(Request $request, TahunAktifService $tahunAktifService, RekapUsulanService $rekapUsulanService): View|RedirectResponse
    {
        try {
            $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);

            if ($tahun < 2020 || $tahun > 2100) {
                return redirect()
                    ->route('admin.penilaian.index', ['tahun' => date('Y')])
                    ->with('error', 'Tahun penilaian tidak valid. Kode Error: PENILAIAN_INVALID_YEAR');
            }

            $dusuns = Dusun::aktif()
                ->orderBy('kode_alternatif')
                ->orderBy('nama_dusun')
                ->get();

            $kriterias = Kriteria::aktif()->ordered()->get();

            $penilaians = PenilaianAlternatif::tahun($tahun)
                ->whereIn('dusun_id', $dusuns->pluck('id'))
                ->whereIn('kriteria_id', $kriterias->pluck('id'))
                ->get();

            $values = [];
            $notes = [];

            foreach ($penilaians as $penilaian) {
                $values[$penilaian->dusun_id][$penilaian->kriteria_id] = $penilaian->nilai;
                $notes[$penilaian->dusun_id][$penilaian->kriteria_id] = $penilaian->keterangan;
            }

            $totalSeharusnya = $dusuns->count() * $kriterias->count();
            $totalTerisi = $penilaians->count();
            $persentaseKelengkapan = $totalSeharusnya > 0
                ? round(($totalTerisi / $totalSeharusnya) * 100, 2)
                : 0;

            return view('admin.penilaian.index', [
                'tahun' => $tahun,
                'dusuns' => $dusuns,
                'kriterias' => $kriterias,
                'values' => $values,
                'notes' => $notes,
                'totalSeharusnya' => $totalSeharusnya,
                'totalTerisi' => $totalTerisi,
                'persentaseKelengkapan' => $persentaseKelengkapan,
                'rekapUsulan' => $rekapUsulanService->perDusun($tahun, $dusuns),
                'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
                'tahunList' => TahunPerencanaan::orderByDesc('tahun')->pluck('tahun'),
            ]);
        } catch (Throwable $e) {
            Log::error('[PENILAIAN_INDEX_FAILED] Gagal memuat penilaian alternatif', $this->logContext($e, $request));

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat penilaian alternatif. Silakan coba kembali. Kode Error: PENILAIAN_INDEX_FAILED');
        }
    }

    public function store(Request $request, RecalculationFlagService $recalculationFlagService): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nilai' => ['required', 'array'],
            'nilai.*' => ['required', 'array'],
            'nilai.*.*' => ['required', 'integer', 'min:1', 'max:5'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'array'],
            'keterangan.*.*' => ['nullable', 'string'],
        ], [
            'nilai.required' => 'Pastikan seluruh nilai alternatif telah diisi dengan skala 1 sampai 5.',
            'nilai.*.*.required' => 'Pastikan seluruh nilai alternatif telah diisi dengan skala 1 sampai 5.',
            'nilai.*.*.integer' => 'Nilai alternatif harus berupa angka 1 sampai 5.',
            'nilai.*.*.min' => 'Nilai alternatif minimal 1.',
            'nilai.*.*.max' => 'Nilai alternatif maksimal 5.',
        ]);

        try {
            $tahun = (int) $validated['tahun'];
            $dusuns = Dusun::aktif()->orderBy('kode_alternatif')->orderBy('nama_dusun')->get();
            $kriterias = Kriteria::aktif()->ordered()->get();

            if ($dusuns->isEmpty()) {
                return back()
                    ->withInput()
                    ->with('error', 'Belum ada dusun aktif. Silakan aktifkan data dusun terlebih dahulu. Kode Error: PENILAIAN_NO_ACTIVE_DUSUN');
            }

            if ($kriterias->isEmpty()) {
                return back()
                    ->withInput()
                    ->with('error', 'Belum ada kriteria aktif. Silakan aktifkan data kriteria terlebih dahulu. Kode Error: PENILAIAN_NO_ACTIVE_KRITERIA');
            }

            $nilaiInput = $validated['nilai'];
            $keteranganInput = $validated['keterangan'] ?? [];

            foreach ($dusuns as $dusun) {
                foreach ($kriterias as $kriteria) {
                    if (! isset($nilaiInput[$dusun->id][$kriteria->id])) {
                        Log::warning('[PENILAIAN_INCOMPLETE] Penilaian alternatif belum lengkap', [
                            'user_id' => auth()->id(),
                            'tahun' => $tahun,
                            'dusun_id' => $dusun->id,
                            'kriteria_id' => $kriteria->id,
                        ]);

                        return back()
                            ->withInput()
                            ->with('error', 'Penilaian belum lengkap. Pastikan seluruh dusun dan kriteria memiliki nilai 1 sampai 5. Kode Error: PENILAIAN_INCOMPLETE');
                    }
                }
            }

            DB::transaction(function () use ($tahun, $dusuns, $kriterias, $nilaiInput, $keteranganInput): void {
                foreach ($dusuns as $dusun) {
                    foreach ($kriterias as $kriteria) {
                        PenilaianAlternatif::updateOrCreate(
                            [
                                'tahun' => $tahun,
                                'dusun_id' => $dusun->id,
                                'kriteria_id' => $kriteria->id,
                            ],
                            [
                                'nilai' => (int) $nilaiInput[$dusun->id][$kriteria->id],
                                'keterangan' => $keteranganInput[$dusun->id][$kriteria->id] ?? null,
                                'created_by' => auth()->id(),
                            ],
                        );
                    }
                }
            });

            $recalculationFlagService->mark($tahun, 'Penilaian alternatif diperbarui.');

            Log::info('[PENILAIAN_SAVED] Penilaian alternatif berhasil disimpan', [
                'user_id' => auth()->id(),
                'tahun' => $tahun,
                'total_dusun' => $dusuns->count(),
                'total_kriteria' => $kriterias->count(),
            ]);

            return redirect()
                ->route('admin.penilaian.index', ['tahun' => $tahun])
                ->with('success', "Penilaian alternatif tahun {$tahun} berhasil disimpan.");
        } catch (Throwable $e) {
            Log::error('[PENILAIAN_STORE_FAILED] Gagal menyimpan penilaian alternatif', $this->logContext($e, $request));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan penilaian alternatif. Silakan coba kembali. Kode Error: PENILAIAN_STORE_FAILED');
        }
    }

    public function preview(Request $request): RedirectResponse
    {
        $tahun = $request->filled('tahun') ? $request->integer('tahun') : (int) date('Y');

        return redirect()->route('admin.penilaian.index', ['tahun' => $tahun]);
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(Throwable $e, Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'tahun' => $request->input('tahun'),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}
