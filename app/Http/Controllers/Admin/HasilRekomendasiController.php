<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectreCalculation;
use App\Models\Kriteria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class HasilRekomendasiController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = ElectreCalculation::with('calculator')
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode_perhitungan', 'like', "%{$keyword}%")
                            ->orWhere('judul', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('tahun'), function ($query) use ($request): void {
                    $query->where('tahun', $request->integer('tahun'));
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                });

            return view('admin.hasil-rekomendasi.index', [
                'calculations' => $query->latest('calculated_at')->latest()->paginate(10)->withQueryString(),
                'statuses' => ElectreCalculation::STATUSES,
                'tahunList' => ElectreCalculation::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
                'stats' => $this->stats(),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => $request->string('tahun')->toString(),
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

    public function pdf(Request $request, ElectreCalculation $electreCalculation)
    {
        try {
            $data = $this->viewData($electreCalculation);
            $data['pdfTitle'] = 'Laporan Hasil Rekomendasi Prioritas Pembangunan Antar Dusun';
            $data['kriterias'] = Kriteria::aktif()->ordered()->get();

            return Pdf::loadView('pdf.hasil-rekomendasi', $data)
                ->setPaper('a4', 'portrait')
                ->stream('laporan-hasil-rekomendasi-'.$electreCalculation->kode_perhitungan.'.pdf');
        } catch (Throwable $e) {
            Log::error('[HASIL_REKOMENDASI_PDF_FAILED] Gagal membuat PDF hasil rekomendasi admin', $this->logContext($e, $request, $electreCalculation));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF hasil rekomendasi. Silakan coba kembali. Kode Error: HASIL_REKOMENDASI_PDF_FAILED');
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

    /**
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        return [
            'total' => ElectreCalculation::count(),
            'selesai' => ElectreCalculation::selesai()->count(),
            'tahun_berjalan' => ElectreCalculation::where('tahun', date('Y'))->count(),
            'terbaru' => ElectreCalculation::latest('calculated_at')->latest()->first(),
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
