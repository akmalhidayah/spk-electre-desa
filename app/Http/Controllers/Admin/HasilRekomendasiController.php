<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\ElectreCalculation;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\TahunPerencanaan;
use App\Models\User;
use App\Models\UsulanPembangunan;
use App\Services\KeputusanAkhirSnapshotService;
use App\Services\PejabatDesaService;
use App\Services\TahunAktifService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class HasilRekomendasiController extends Controller
{
    public function index(Request $request, TahunAktifService $tahunAktifService): View|RedirectResponse
    {
        try {
            $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);
            $query = ElectreCalculation::with(['calculator', 'keputusanAkhir'])
                ->tahun($tahun)
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode_perhitungan', 'like', "%{$keyword}%")
                            ->orWhere('judul', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                });

            return view('admin.hasil-rekomendasi.index', [
                'calculations' => $query->latest('calculated_at')->latest()->paginate(10)->withQueryString(),
                'statuses' => ElectreCalculation::STATUSES,
                'tahunList' => TahunPerencanaan::orderByDesc('tahun')->pluck('tahun'),
                'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
                'stats' => $this->stats($tahun),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => (string) $tahun,
                    'status' => $request->string('status')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_INDEX_FAILED] Gagal memuat hasil rekomendasi admin', $this->logContext($e, $request));

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat hasil rekomendasi. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_INDEX_FAILED');
        }
    }

    public function show(Request $request, ElectreCalculation $electreCalculation): View|RedirectResponse
    {
        try {
            return view('admin.hasil-rekomendasi.show', $this->viewData($electreCalculation));
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_SHOW_FAILED] Gagal memuat detail hasil rekomendasi admin', $this->logContext($e, $request, $electreCalculation));

            return redirect()
                ->route('admin.hasil-rekomendasi.index')
                ->with('error', 'Terjadi kesalahan saat memuat detail hasil rekomendasi. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_SHOW_FAILED');
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

            $data = $this->viewData($electreCalculation);
            $data['pdfTitle'] = 'Laporan Hasil Rekomendasi Prioritas Pembangunan Antar Dusun';
            $data['kriterias'] = Kriteria::aktif()->ordered()->get();
            $data['acceptedUsulans'] = $this->acceptedUsulansForYear($tahun);
            $data['kepalaDesaName'] = $pejabatDesaService->kepalaDesaName();

            return Pdf::loadView('pdf.hasil-rekomendasi', $data)
                ->setPaper('a4', 'portrait')
                ->stream('laporan-hasil-rekomendasi-'.$tahun.'.pdf');
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_PDF_FAILED] Gagal membuat PDF hasil rekomendasi admin', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF hasil rekomendasi. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_PDF_FAILED');
        }
    }

    public function calculationPdf(Request $request, ElectreCalculation $electreCalculation, PejabatDesaService $pejabatDesaService)
    {
        try {
            if ($electreCalculation->status !== ElectreCalculation::STATUS_SELESAI) {
                return back()->with('error', 'Hasil rekomendasi ini belum selesai.');
            }

            $data = $this->viewData($electreCalculation);
            $data['pdfTitle'] = 'Laporan Hasil Rekomendasi Prioritas Pembangunan Antar Dusun';
            $data['kriterias'] = Kriteria::aktif()->ordered()->get();
            $data['acceptedUsulans'] = $this->acceptedUsulansForYear((int) $electreCalculation->tahun);
            $data['kepalaDesaName'] = $pejabatDesaService->kepalaDesaName();

            return Pdf::loadView('pdf.hasil-rekomendasi', $data)
                ->setPaper('a4', 'portrait')
                ->stream('laporan-hasil-rekomendasi-'.$electreCalculation->tahun.'-v'.$electreCalculation->versi.'.pdf');
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_PERHITUNGAN_PDF_FAILED] Gagal membuat PDF hasil rekomendasi versi tertentu admin', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF hasil rekomendasi versi perhitungan. Kode Error: HASIL_REKOMENDASI_PERHITUNGAN_PDF_FAILED');
        }
    }

    public function keputusanPdf(Request $request, ElectreCalculation $electreCalculation, KeputusanAkhirSnapshotService $snapshotService)
    {
        try {
            $keputusanAkhir = $electreCalculation->keputusanAkhir()
                ->first();

            if (! $keputusanAkhir) {
                return back()->with('error', 'Keputusan akhir untuk hasil rekomendasi ini belum dibuat.');
            }

            return $this->streamKeputusanPdf($keputusanAkhir, $snapshotService);
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_KEPUTUSAN_PDF_FAILED] Gagal membuat PDF keputusan akhir admin', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF keputusan akhir. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_KEPUTUSAN_PDF_FAILED');
        }
    }

    public function keputusanAkhirPdf(KeputusanAkhir $keputusanAkhir, KeputusanAkhirSnapshotService $snapshotService)
    {
        return $this->streamKeputusanPdf($keputusanAkhir, $snapshotService);
    }

    public function dusunPdf(Request $request, ElectreCalculation $electreCalculation, Dusun $dusun, PejabatDesaService $pejabatDesaService)
    {
        try {
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
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_DUSUN_PDF_FAILED] Gagal membuat PDF usulan per dusun admin', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF usulan dusun. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_DUSUN_PDF_FAILED');
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
            'matriksKeputusan' => $details->get('matriks_keputusan')?->data ?? [],
            'normalisasi' => $details->get('normalisasi')?->data ?? [],
            'pembobotan' => $details->get('pembobotan')?->data ?? [],
            'threshold' => $details->get('threshold')?->data ?? [],
            'aggregateDominance' => $details->get('aggregate_dominance')?->data ?? [],
            'rankingSummary' => $details->get('ranking_summary')?->data ?? [],
        ];
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
            ->diterimaAtauPrioritas()
            ->orderBy('dusun_id')
            ->orderBy('nama_kegiatan')
            ->get();
    }

    private function acceptedUsulansForDusun(ElectreCalculation $calculation, Dusun $dusun)
    {
        return UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
            ->tahun((int) $calculation->tahun)
            ->diterimaAtauPrioritas()
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

    private function streamKeputusanPdf(KeputusanAkhir $keputusanAkhir, KeputusanAkhirSnapshotService $snapshotService)
    {
        if ($keputusanAkhir->status === KeputusanAkhir::STATUS_DITETAPKAN) {
            $keputusanAkhir = $snapshotService->storePdfFromSnapshot($keputusanAkhir);
            $path = $snapshotService->pdfStoragePath($keputusanAkhir);

            if ($path) {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="keputusan-akhir-'.$keputusanAkhir->id.'.pdf"',
                ]);
            }
        }

        return Pdf::loadView('pdf.keputusan-akhir', $snapshotService->pdfViewData($keputusanAkhir))
            ->setPaper('a4', 'portrait')
            ->stream('draft-keputusan-akhir-'.$keputusanAkhir->id.'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(int $tahun): array
    {
        return [
            'total' => ElectreCalculation::tahun($tahun)->count(),
            'selesai' => ElectreCalculation::tahun($tahun)->selesai()->count(),
            'tahun_berjalan' => ElectreCalculation::where('tahun', $tahun)->count(),
            'terbaru' => ElectreCalculation::tahun($tahun)->latestVersion()->latest('calculated_at')->latest()->first(),
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
