<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HasilRekomendasiController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = ElectreCalculation::with('calculator')
                ->selesai()
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode_perhitungan', 'like', "%{$keyword}%")
                            ->orWhere('judul', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('tahun'), function ($query) use ($request): void {
                    $query->where('tahun', $request->integer('tahun'));
                });

            return view('kepala-desa.hasil-rekomendasi.index', [
                'calculations' => $query->latest('calculated_at')->latest()->paginate(10)->withQueryString(),
                'tahunList' => ElectreCalculation::selesai()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
                'stats' => $this->stats(),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => $request->string('tahun')->toString(),
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

    public function pdf(Request $request, ElectreCalculation $electreCalculation)
    {
        try {
            $this->ensureFinished($electreCalculation);

            $data = $this->viewData($electreCalculation);
            $data['pdfTitle'] = 'Laporan Keputusan Prioritas Pembangunan';
            $data['kriterias'] = Kriteria::aktif()->ordered()->get();

            return Pdf::loadView('pdf.hasil-rekomendasi', $data)
                ->setPaper('a4', 'portrait')
                ->stream('laporan-hasil-rekomendasi-'.$electreCalculation->kode_perhitungan.'.pdf');
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[KEPALA_DESA_HASIL_PDF_FAILED] Gagal membuat PDF hasil rekomendasi kepala desa', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF hasil rekomendasi. Silakan coba kembali. Kode Error: KEPALA_DESA_HASIL_PDF_FAILED');
        }
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
    private function stats(): array
    {
        return [
            'total' => ElectreCalculation::selesai()->count(),
            'terbaru' => ElectreCalculation::selesai()->latest('calculated_at')->latest()->first(),
            'tahun_berjalan' => ElectreCalculation::selesai()->where('tahun', date('Y'))->count(),
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
