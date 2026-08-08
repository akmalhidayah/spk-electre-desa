<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\PenilaianAlternatif;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use App\Services\ElectreService;
use App\Services\TahunAktifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ElectreCalculationController extends Controller
{
    public function index(Request $request, TahunAktifService $tahunAktifService): View|RedirectResponse
    {
        try {
            $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);

            if ($tahun < 2020 || $tahun > 2100) {
                return redirect()
                    ->route('admin.electre.index', ['tahun' => date('Y')])
                    ->with('error', 'Tahun penilaian tidak valid. Kode Error: ELECTRE_INVALID_YEAR');
            }

            $summary = $this->buildReadinessSummary($tahun);
            $histories = ElectreCalculation::with('calculator')
                ->tahun($tahun)
                ->latest('calculated_at')
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('admin.electre.index', [
                'tahun' => $tahun,
                'summary' => $summary,
                'histories' => $histories,
                'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
                'tahunList' => TahunPerencanaan::orderByDesc('tahun')->pluck('tahun'),
            ]);
        } catch (Throwable $e) {
            Log::error('[ELECTRE_INDEX_FAILED] Gagal memuat halaman proses ELECTRE', $this->logContext($e, $request));

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat proses ELECTRE. Silakan coba kembali. Kode Error: ELECTRE_INDEX_FAILED');
        }
    }

    public function process(Request $request, ElectreService $electreService): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        try {
            $calculation = $electreService->calculate((int) $validated['tahun'], $request->user()->id);

            return redirect()
                ->route('admin.electre.show', $calculation)
                ->with('success', 'Perhitungan ELECTRE berhasil diproses.');
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('[ELECTRE_PROCESS_FAILED] Gagal memproses ELECTRE', $this->logContext($e, $request));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses ELECTRE. Kode Error: ELECTRE_PROCESS_FAILED');
        }
    }

    public function show(Request $request, ElectreCalculation $electreCalculation): View|RedirectResponse
    {
        try {
            $electreCalculation->load([
                'results.program.dusun',
                'results.program.dusunsTerkait',
                'details',
                'calculator',
            ]);

            return view('admin.electre.show', [
                'calculation' => $electreCalculation,
                'details' => $electreCalculation->details->keyBy('tahap'),
            ]);
        } catch (Throwable $e) {
            Log::error('[ELECTRE_SHOW_FAILED] Gagal memuat hasil ELECTRE', $this->logContext($e, $request, $electreCalculation));

            return redirect()
                ->route('admin.electre.index')
                ->with('error', 'Terjadi kesalahan saat memuat hasil ELECTRE. Silakan coba kembali. Kode Error: ELECTRE_SHOW_FAILED');
        }
    }

    public function destroy(Request $request, ElectreCalculation $electreCalculation): RedirectResponse
    {
        try {
            if ($electreCalculation->keputusanAkhir()->exists()) {
                return back()->with('error', 'Perhitungan ini sudah memiliki keputusan akhir dan tidak boleh dihapus.');
            }

            $electreCalculation->delete();

            Log::info('[ELECTRE_DELETED] Histori perhitungan ELECTRE berhasil dihapus', [
                'user_id' => $request->user()->id,
                'calculation_id' => $electreCalculation->id,
                'tahun' => $electreCalculation->tahunPerencanaan?->tahun,
            ]);

            return redirect()
                ->route('admin.electre.index')
                ->with('success', 'Histori perhitungan ELECTRE berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[ELECTRE_DELETE_FAILED] Gagal menghapus histori ELECTRE', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat menghapus histori ELECTRE. Kode Error: ELECTRE_DELETE_FAILED');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReadinessSummary(int $tahun): array
    {
        $periode = TahunPerencanaan::where('tahun', $tahun)->first();
        $programIds = $periode ? UsulanPembangunan::periode($periode->id)->diterima()->pluck('id') : collect();
        $kriteriaIds = Kriteria::aktif()->pluck('id');
        $totalDusunAktif = $programIds->count();
        $totalKriteriaAktif = $kriteriaIds->count();
        $totalBobotAktif = (float) Kriteria::aktif()->sum('bobot');
        $totalPenilaianSeharusnya = $totalDusunAktif * $totalKriteriaAktif;
        $totalPenilaianTerisi = $periode ? PenilaianAlternatif::periode($periode->id)
            ->whereIn('usulan_pembangunan_id', $programIds)
            ->whereIn('kriteria_id', $kriteriaIds)
            ->whereBetween('nilai', [PenilaianAlternatif::NILAI_MIN, PenilaianAlternatif::NILAI_MAX])
            ->count() : 0;
        $persentaseKelengkapan = $totalPenilaianSeharusnya > 0
            ? round(($totalPenilaianTerisi / $totalPenilaianSeharusnya) * 100, 2)
            : 0;

        $reasons = [];

        if ($totalDusunAktif < 2) {
            $reasons[] = 'Minimal harus terdapat dua program diterima.';
        }

        if ($totalKriteriaAktif < 1) {
            $reasons[] = 'Belum ada kriteria aktif.';
        }

        if (abs($totalBobotAktif - 100.0) > 0.01) {
            $reasons[] = 'Total bobot kriteria aktif harus 100%.';
        }

        if ($totalPenilaianTerisi !== $totalPenilaianSeharusnya) {
            $reasons[] = 'Lengkapi penilaian alternatif terlebih dahulu sebelum memproses ELECTRE.';
        }

        return [
            'total_dusun_aktif' => $totalDusunAktif,
            'total_program_dinilai' => $totalDusunAktif,
            'total_kriteria_aktif' => $totalKriteriaAktif,
            'total_bobot_aktif' => $totalBobotAktif,
            'total_penilaian_terisi' => $totalPenilaianTerisi,
            'total_penilaian_seharusnya' => $totalPenilaianSeharusnya,
            'persentase_kelengkapan' => $persentaseKelengkapan,
            'is_ready' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(Throwable $e, Request $request, ?ElectreCalculation $calculation = null): array
    {
        return [
            'user_id' => $request->user()?->id,
            'tahun' => $request->input('tahun') ?? $calculation?->tahunPerencanaan?->tahun,
            'calculation_id' => $calculation?->id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}
