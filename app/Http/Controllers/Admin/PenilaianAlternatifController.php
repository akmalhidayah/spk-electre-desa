<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
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

            $periode = TahunPerencanaan::where('tahun', $tahun)->firstOrFail();
            $dusuns = UsulanPembangunan::with(['dusun', 'dusunsTerkait'])->periode($periode->id)->diterima()->orderBy('id')->get();

            $kriterias = Kriteria::aktif()->ordered()->get();

            $penilaians = PenilaianAlternatif::periode($periode->id)
                ->whereIn('usulan_pembangunan_id', $dusuns->pluck('id'))
                ->whereIn('kriteria_id', $kriterias->pluck('id'))
                ->get();

            $values = [];
            $notes = [];

            foreach ($penilaians as $penilaian) {
                $values[$penilaian->usulan_pembangunan_id][$penilaian->kriteria_id] = $penilaian->nilai;
                $notes[$penilaian->usulan_pembangunan_id][$penilaian->kriteria_id] = $penilaian->keterangan;
            }

            $totalSeharusnya = $dusuns->count() * $kriterias->count();
            $totalTerisi = $penilaians->count();
            $totalProgramLengkap = $penilaians
                ->groupBy('usulan_pembangunan_id')
                ->filter(fn ($items) => $items->pluck('kriteria_id')->unique()->count() === $kriterias->count())
                ->count();
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
                'totalProgramLengkap' => $totalProgramLengkap,
                'totalProgram' => $dusuns->count(),
                'persentaseKelengkapan' => $persentaseKelengkapan,
                'rekapUsulan' => $rekapUsulanService->perDusun($tahun),
                'periode' => $periode,
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
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['nullable', 'array'],
            'nilai.*.*' => ['nullable', 'integer', 'min:1', 'max:5'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'array'],
            'keterangan.*.*' => ['nullable', 'string'],
        ], [
            'nilai.*.*.integer' => 'Nilai alternatif harus berupa angka 1 sampai 5.',
            'nilai.*.*.min' => 'Nilai alternatif minimal 1.',
            'nilai.*.*.max' => 'Nilai alternatif maksimal 5.',
        ]);

        try {
            $tahun = (int) $validated['tahun'];
            $periode = TahunPerencanaan::where('tahun', $tahun)->firstOrFail();
            $dusuns = UsulanPembangunan::with(['dusun', 'dusunsTerkait'])->periode($periode->id)->diterima()->orderBy('id')->get();
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

            $nilaiInput = $validated['nilai'] ?? [];
            $keteranganInput = $validated['keterangan'] ?? [];

            DB::transaction(function () use ($periode, $dusuns, $kriterias, $nilaiInput, $keteranganInput): void {
                foreach ($dusuns as $dusun) {
                    foreach ($kriterias as $kriteria) {
                        $nilai = data_get($nilaiInput, "{$dusun->id}.{$kriteria->id}");

                        if ($nilai === null || $nilai === '') {
                            continue;
                        }

                        PenilaianAlternatif::updateOrCreate(
                            [
                                'tahun_perencanaan_id' => $periode->id,
                                'usulan_pembangunan_id' => $dusun->id,
                                'kriteria_id' => $kriteria->id,
                            ],
                            [
                                'nilai' => (int) $nilai,
                                'keterangan' => $keteranganInput[$dusun->id][$kriteria->id] ?? null,
                                'created_by' => auth()->id(),
                            ],
                        );
                    }
                }
            });

            $recalculationFlagService->mark($tahun, 'Penilaian alternatif diperbarui.');

            $totalProgramLengkap = PenilaianAlternatif::periode($periode->id)
                ->whereIn('usulan_pembangunan_id', $dusuns->pluck('id'))
                ->whereIn('kriteria_id', $kriterias->pluck('id'))
                ->get()
                ->groupBy('usulan_pembangunan_id')
                ->filter(fn ($items) => $items->pluck('kriteria_id')->unique()->count() === $kriterias->count())
                ->count();

            Log::info('[PENILAIAN_SAVED] Penilaian alternatif berhasil disimpan', [
                'user_id' => auth()->id(),
                'tahun' => $tahun,
                'total_program' => $dusuns->count(),
                'total_kriteria' => $kriterias->count(),
            ]);

            return redirect()
                ->route('admin.penilaian.index', ['tahun' => $tahun])
                ->with('success', "Penilaian berhasil disimpan. {$totalProgramLengkap} dari {$dusuns->count()} program telah dinilai lengkap.");
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
