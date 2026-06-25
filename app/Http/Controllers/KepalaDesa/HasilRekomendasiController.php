<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use App\Models\User;
use App\Services\PejabatDesaService;
use App\Services\TahunAktifService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HasilRekomendasiController extends Controller
{
    public function index(Request $request, TahunAktifService $tahunAktifService): View|RedirectResponse
    {
        try {
            $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
            $query = ElectreCalculation::with(['calculator', 'keputusanAkhir'])
                ->selesai()
                ->tahun($tahun)
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode_perhitungan', 'like', "%{$keyword}%")
                            ->orWhere('judul', 'like', "%{$keyword}%");
                    });
                });

            return view('kepala-desa.hasil-rekomendasi.index', [
                'calculations' => $query->latest('calculated_at')->latest()->paginate(10)->withQueryString(),
                'tahunList' => TahunPerencanaan::orderByDesc('tahun')->pluck('tahun'),
                'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
                'stats' => $this->stats($tahun),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => (string) $tahun,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[KEPALA_DESA_HASIL_INDEX_FAILED] Gagal memuat hasil rekomendasi kepala desa', $this->logContext($e, $request));

            return redirect()
                ->route('kepala-desa.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat hasil rekomendasi. Silakan coba kembali. Kode Error: KEPALA_DESA_HASIL_INDEX_FAILED');
        }
    }

    public function show(Request $request, ElectreCalculation $electreCalculation): View|RedirectResponse
    {
        try {
            $this->ensureFinished($electreCalculation);

            return view('kepala-desa.hasil-rekomendasi.show', $this->viewData($electreCalculation));
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[KEPALA_DESA_HASIL_SHOW_FAILED] Gagal memuat detail hasil rekomendasi kepala desa', $this->logContext($e, $request, $electreCalculation));

            return redirect()
                ->route('kepala-desa.hasil-rekomendasi.index')
                ->with('error', 'Hasil rekomendasi tidak dapat diakses. Kode Error: KEPALA_DESA_HASIL_SHOW_FAILED');
        }
    }

    public function pdf(Request $request, int $tahun, PejabatDesaService $pejabatDesaService)
    {
        $electreCalculation = null;

        try {
            $electreCalculation = $this->latestFinishedCalculationForYear($tahun);

            if (! $electreCalculation) {
                return back()->with('error', "Hasil rekomendasi selesai untuk tahun {$tahun} belum tersedia.");
            }

            $this->ensureFinished($electreCalculation);

            $data = $this->viewData($electreCalculation);
            $data['pdfTitle'] = 'Laporan Keputusan Prioritas Pembangunan';
            $data['kriterias'] = Kriteria::aktif()->ordered()->get();
            $data['acceptedUsulans'] = $this->acceptedUsulansForYear($tahun);
            $data['kepalaDesaName'] = $pejabatDesaService->kepalaDesaName();

            return Pdf::loadView('pdf.hasil-rekomendasi', $data)
                ->setPaper('a4', 'portrait')
                ->stream('laporan-hasil-rekomendasi-'.$tahun.'.pdf');
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[KEPALA_DESA_HASIL_PDF_FAILED] Gagal membuat PDF hasil rekomendasi kepala desa', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF hasil rekomendasi. Silakan coba kembali. Kode Error: KEPALA_DESA_HASIL_PDF_FAILED');
        }
    }

    public function dusunPdf(Request $request, ElectreCalculation $electreCalculation, Dusun $dusun, PejabatDesaService $pejabatDesaService)
    {
        try {
            $this->ensureFinished($electreCalculation);

            $usulans = $this->acceptedUsulansForDusun($electreCalculation, $dusun);
            $kepalaDusunName = $pejabatDesaService->kepalaDusunName($dusun) ?? $this->kepalaDusunForDusun($dusun)?->name;

            return Pdf::loadView('pdf.usulan-diterima-dusun', [
                'pdfTitle' => 'Daftar Usulan Pembangunan Diterima '.$dusun->nama_dusun,
                'calculation' => $electreCalculation,
                'dusun' => $dusun,
                'kepalaDusunName' => $kepalaDusunName,
                'usulans' => $usulans,
            ])
                ->setPaper('a4', 'portrait')
                ->stream('usulan-diterima-'.$electreCalculation->tahun.'-'.$dusun->id.'.pdf');
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[KEPALA_DESA_HASIL_DUSUN_PDF_FAILED] Gagal membuat PDF usulan per dusun kepala desa', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF usulan dusun. Silakan coba kembali. Kode Error: KEPALA_DESA_HASIL_DUSUN_PDF_FAILED');
        }
    }

    private function latestFinishedCalculationForYear(int $tahun): ?ElectreCalculation
    {
        return ElectreCalculation::tahun($tahun)
            ->selesai()
            ->latestVersion()
            ->latest('calculated_at')
            ->latest()
            ->first()
            ?? ElectreCalculation::tahun($tahun)
                ->selesai()
                ->latest('calculated_at')
                ->latest()
                ->first();
    }

    private function acceptedUsulansForYear(int $tahun)
    {
        return UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
            ->tahun($tahun)
            ->diterima()
            ->orderBy('dusun_id')
            ->orderBy('nama_kegiatan')
            ->get();
    }

    private function acceptedUsulansForDusun(ElectreCalculation $calculation, Dusun $dusun)
    {
        return UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
            ->tahun((int) $calculation->tahun)
            ->diterima()
            ->where(function ($query) use ($dusun): void {
                $query->where('dusun_id', $dusun->id)
                    ->orWhereHas('dusunsTerkait', fn ($query) => $query->where('dusuns.id', $dusun->id));
            })
            ->orderBy('nama_kegiatan')
            ->get();
    }

    private function kepalaDusunForDusun(Dusun $dusun): ?User
    {
        return User::aktif()
            ->role(User::ROLE_KEPALA_DUSUN)
            ->where('dusun_id', $dusun->id)
            ->first();
    }

    private function ensureFinished(ElectreCalculation $calculation): void
    {
        if ($calculation->status !== ElectreCalculation::STATUS_SELESAI) {
            Log::warning('[KEPALA_DESA_HASIL_FORBIDDEN] Kepala desa mencoba mengakses hasil belum selesai', [
                'calculation_id' => $calculation->id,
                'kode_perhitungan' => $calculation->kode_perhitungan,
                'status' => $calculation->status,
            ]);

            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(ElectreCalculation $calculation): array
    {
        $calculation->load(['results.dusun', 'details', 'calculator']);
        $details = $calculation->details->keyBy('tahap');

        return [
            'calculation' => $calculation,
            'results' => $calculation->results->sortBy('ranking')->values(),
            'details' => $details,
            'threshold' => $details->get('threshold')?->data ?? [],
            'aggregateDominance' => $details->get('aggregate_dominance')?->data ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(int $tahun): array
    {
        return [
            'total' => ElectreCalculation::tahun($tahun)->selesai()->count(),
            'terbaru' => ElectreCalculation::tahun($tahun)->selesai()->latestVersion()->latest('calculated_at')->latest()->first(),
            'tahun_berjalan' => ElectreCalculation::tahun($tahun)->selesai()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(Throwable $e, Request $request, ?ElectreCalculation $calculation = null): array
    {
        return [
            'user_id' => $request->user()?->id,
            'calculation_id' => $calculation?->id,
            'kode_perhitungan' => $calculation?->kode_perhitungan,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}
