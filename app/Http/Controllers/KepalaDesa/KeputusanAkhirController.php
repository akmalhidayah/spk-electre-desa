<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKeputusanAkhirRequest;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\TahunPerencanaan;
use App\Services\BudgetAllocationService;
use App\Services\KeputusanAkhirSnapshotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class KeputusanAkhirController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = KeputusanAkhir::with(['calculation.tahunPerencanaan', 'program.dusun', 'details', 'penetap'])
                ->whereIn('status', [KeputusanAkhir::STATUS_DRAFT, KeputusanAkhir::STATUS_DITETAPKAN])
                ->latest();

            if ($request->filled('tahun')) {
                $query->whereHas('tahunPerencanaan', fn ($periode) => $periode->where('tahun', $request->integer('tahun')));
            }

            if ($request->filled('status')) {
                $query->where('status', (string) $request->string('status'));
            }

            if ($request->filled('q')) {
                $keyword = (string) $request->string('q');
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('nomor_keputusan', 'like', "%{$keyword}%")
                        ->orWhereHas('calculation', function ($calculationQuery) use ($keyword): void {
                            $calculationQuery
                                ->where('kode_perhitungan', 'like', "%{$keyword}%")
                                ->orWhere('judul', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('program', function ($programQuery) use ($keyword): void {
                            $programQuery->where('nama_kegiatan', 'like', "%{$keyword}%");
                        });
                });
            }

            return view('kepala-desa.keputusan-akhir.index', [
                'keputusans' => $query->paginate(10)->withQueryString(),
                'totalKeputusan' => KeputusanAkhir::whereIn('status', [KeputusanAkhir::STATUS_DRAFT, KeputusanAkhir::STATUS_DITETAPKAN])->count(),
                'totalDraft' => KeputusanAkhir::where('status', KeputusanAkhir::STATUS_DRAFT)->count(),
                'totalDitetapkan' => KeputusanAkhir::where('status', KeputusanAkhir::STATUS_DITETAPKAN)->count(),
            ]);
        } catch (Throwable $e) {
            Log::error('[KEPUTUSAN_AKHIR_INDEX_FAILED] Gagal menampilkan daftar keputusan akhir', [
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('kepala-desa.dashboard')
                ->with('error', 'Terjadi kesalahan saat menampilkan laporan keputusan. Kode Error: KEPUTUSAN_AKHIR_INDEX_FAILED');
        }
    }

    public function create(ElectreCalculation $electreCalculation, BudgetAllocationService $budgetService): View|RedirectResponse
    {
        $electreCalculation->load(['results.program.dusun', 'results.program.dusunsTerkait', 'keputusanAkhir', 'tahunPerencanaan']);

        if (! $budgetService->isOfficialCalculation($electreCalculation)) {
            return redirect()
                ->route('kepala-desa.hasil-rekomendasi.index')
                ->with('error', 'Keputusan hanya dapat dibuat dari perhitungan reguler terbaru yang sudah selesai. Kode Error: KEPUTUSAN_AKHIR_INVALID_CALCULATION');
        }

        if ($electreCalculation->keputusanAkhir) {
            return redirect()
                ->route('kepala-desa.keputusan-akhir.show', $electreCalculation->keputusanAkhir)
                ->with('error', 'Keputusan akhir untuk perhitungan ini sudah dibuat. Kode Error: KEPUTUSAN_AKHIR_DUPLICATE');
        }

        $budget = $budgetService->simulate($electreCalculation->tahunPerencanaan, $electreCalculation);

        if ($budget['summary']['pagu'] === null) {
            return redirect()->route('kepala-desa.hasil-rekomendasi.show', $electreCalculation)
                ->with('error', 'Pagu anggaran pembangunan tahun ini belum diatur.');
        }

        return view('kepala-desa.keputusan-akhir.create', [
            'calculation' => $electreCalculation,
            'results' => $budget['results'],
            'budgetSummary' => $budget['summary'],
        ]);
    }

    public function store(StoreKeputusanAkhirRequest $request, KeputusanAkhirSnapshotService $snapshotService, BudgetAllocationService $budgetService): RedirectResponse
    {
        try {
            $data = $request->validated();

            $keputusan = DB::transaction(function () use ($data, $request, $snapshotService, $budgetService): KeputusanAkhir {
                $calculation = ElectreCalculation::whereKey($data['electre_calculation_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $budgetService->isOfficialCalculation($calculation)) {
                    throw new RuntimeException('Keputusan hanya dapat dibuat dari perhitungan reguler terbaru yang sudah selesai. Kode Error: KEPUTUSAN_AKHIR_INVALID_CALCULATION');
                }

                $periode = TahunPerencanaan::whereKey($calculation->tahun_perencanaan_id)->lockForUpdate()->firstOrFail();

                $existing = KeputusanAkhir::where('electre_calculation_id', $calculation->id)
                    ->whereIn('status', [KeputusanAkhir::STATUS_DRAFT, KeputusanAkhir::STATUS_DITETAPKAN])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    throw new RuntimeException('Keputusan akhir untuk perhitungan ini sudah dibuat. Kode Error: KEPUTUSAN_AKHIR_DUPLICATE');
                }

                $results = ElectreResult::with('program.dusun')
                    ->where('electre_calculation_id', $calculation->id)
                    ->whereIn('id', $data['electre_result_ids'])
                    ->lockForUpdate()
                    ->get()
                    ->sortBy('ranking')
                    ->values();

                if ($results->count() !== count($data['electre_result_ids'])) {
                    throw new RuntimeException('Program yang dipilih tidak terdapat pada hasil rekomendasi ini. Kode Error: KEPUTUSAN_AKHIR_INVALID_PROGRAM');
                }

                $summary = $budgetService->summary($periode);
                if ($summary['pagu'] === null) {
                    throw new RuntimeException('Pagu anggaran pembangunan tahun ini belum diatur.');
                }

                $alreadyDetermined = collect($summary['program_ids_ditetapkan']);
                if ($results->contains(fn ($result) => $alreadyDetermined->contains((int) $result->usulan_pembangunan_id))) {
                    throw new RuntimeException('Salah satu program yang dipilih sudah ditetapkan pada keputusan lain.');
                }

                $amounts = $budgetService->amountMap($calculation);
                if ($results->contains(fn ($result) => $amounts->get($result->id) === null)) {
                    throw new RuntimeException('Jumlah anggaran seluruh program yang dipilih harus sudah diisi.');
                }

                $selectedTotal = (float) $results->sum(fn ($result) => $amounts->get($result->id));
                if ($selectedTotal > (float) $summary['sisa_pagu']) {
                    throw new RuntimeException('Total anggaran program yang dipilih melebihi sisa pagu. Total dipilih: Rp'.number_format($selectedTotal, 0, ',', '.').'. Sisa pagu: Rp'.number_format((float) $summary['sisa_pagu'], 0, ',', '.').'.');
                }

                $result = $results->first();

                $payload = [
                    'electre_calculation_id' => $calculation->id,
                    'electre_result_id' => $result->id,
                    'usulan_pembangunan_id' => $result->usulan_pembangunan_id,
                    'tahun_perencanaan_id' => $calculation->tahun_perencanaan_id,
                    'ditetapkan_oleh' => $request->user()->id,
                    'nomor_keputusan' => $data['nomor_keputusan'] ?? null,
                    'tanggal_keputusan' => $data['tanggal_keputusan'],
                    'status' => $data['status'],
                    'dasar_pertimbangan' => $data['dasar_pertimbangan'] ?? null,
                    'catatan_keputusan' => $data['catatan_keputusan'] ?? null,
                    'tanda_tangan' => $data['tanda_tangan'] ?? null,
                ];

                $payload['decided_at'] = $data['status'] === KeputusanAkhir::STATUS_DITETAPKAN ? now() : null;

                $keputusan = KeputusanAkhir::create($payload);

                foreach ($results as $selectedResult) {
                    $keputusan->details()->create([
                        'electre_result_id' => $selectedResult->id,
                        'usulan_pembangunan_id' => $selectedResult->usulan_pembangunan_id,
                        'kode_alternatif_snapshot' => $selectedResult->kode_alternatif,
                        'nama_program_snapshot' => $selectedResult->nama_program_snapshot,
                        'lokasi_snapshot' => $selectedResult->lokasi_snapshot,
                        'nama_dusun_snapshot' => $selectedResult->nama_dusun_snapshot,
                        'ranking_snapshot' => $selectedResult->ranking,
                        'skor_dominasi_snapshot' => $selectedResult->skor_dominasi,
                        'estimasi_anggaran_snapshot' => $amounts->get($selectedResult->id),
                    ]);
                }

                if ($keputusan->status === KeputusanAkhir::STATUS_DITETAPKAN) {
                    $keputusan = $snapshotService->saveSnapshot($keputusan);
                }

                return $keputusan;
            });

            if ($keputusan->status === KeputusanAkhir::STATUS_DITETAPKAN) {
                $keputusan = $snapshotService->storePdfFromSnapshot($keputusan);
            }

            Log::info('[KEPUTUSAN_AKHIR_CREATED] Keputusan akhir berhasil disimpan', [
                'user_id' => $request->user()->id,
                'keputusan_id' => $keputusan->id,
                'calculation_id' => $keputusan->electre_calculation_id,
                'usulan_pembangunan_id' => $keputusan->usulan_pembangunan_id,
                'status' => $keputusan->status,
            ]);

            return redirect()
                ->route('kepala-desa.keputusan-akhir.show', $keputusan)
                ->with('success', 'Keputusan akhir berhasil disimpan.');
        } catch (QueryException $e) {
            Log::warning('[KEPUTUSAN_AKHIR_DUPLICATE_QUERY] Unique constraint mencegah keputusan akhir ganda', [
                'user_id' => $request->user()?->id,
                'calculation_id' => $request->input('electre_calculation_id'),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Keputusan akhir untuk perhitungan ini sudah dibuat.');
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('[KEPUTUSAN_AKHIR_STORE_FAILED] Gagal menyimpan keputusan akhir', [
                'user_id' => $request->user()?->id,
                'calculation_id' => $request->input('electre_calculation_id'),
                'electre_result_ids' => $request->input('electre_result_ids'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan keputusan akhir. Kode Error: KEPUTUSAN_AKHIR_STORE_FAILED');
        }
    }

    public function show(Request $request, KeputusanAkhir $keputusanAkhir)
    {
        try {
            if ($request->boolean('pdf')) {
                return redirect()->route('kepala-desa.keputusan-akhir.pdf', $keputusanAkhir);
            }

            $data = $this->viewData($keputusanAkhir);

            return view('kepala-desa.keputusan-akhir.show', $data);
        } catch (Throwable $e) {
            Log::error('[KEPUTUSAN_AKHIR_SHOW_FAILED] Gagal menampilkan keputusan akhir', [
                'user_id' => $request->user()?->id,
                'keputusan_id' => $keputusanAkhir->id,
                'pdf' => $request->boolean('pdf'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('kepala-desa.keputusan-akhir.index')
                ->with('error', 'Keputusan akhir tidak dapat ditampilkan. Kode Error: KEPUTUSAN_AKHIR_SHOW_FAILED');
        }
    }

    public function pdf(KeputusanAkhir $keputusanAkhir, KeputusanAkhirSnapshotService $snapshotService)
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
    private function viewData(KeputusanAkhir $keputusanAkhir): array
    {
        $keputusanAkhir->load(['calculation.results.program.dusun', 'calculation.results.program.dusunsTerkait', 'calculation.calculator', 'calculation.tahunPerencanaan', 'program.dusun', 'program.dusunsTerkait', 'details.program', 'penetap', 'result']);

        return [
            'keputusan' => $keputusanAkhir,
            'calculation' => $keputusanAkhir->calculation,
            'results' => $keputusanAkhir->calculation?->results?->sortBy('ranking')->values() ?? collect(),
            'selectedDetails' => $keputusanAkhir->details->isNotEmpty() ? $keputusanAkhir->details : collect([$keputusanAkhir->result]),
            'budgetSummary' => data_get($keputusanAkhir->snapshot_data, 'budget_summary', []),
        ];
    }
}
