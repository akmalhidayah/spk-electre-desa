<?php

namespace App\Http\Controllers\KepalaDesa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKeputusanAkhirRequest;
use App\Models\ElectreCalculation;
use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class KeputusanAkhirController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = KeputusanAkhir::with(['calculation', 'dusun', 'penetap'])
                ->whereIn('status', [KeputusanAkhir::STATUS_DRAFT, KeputusanAkhir::STATUS_DITETAPKAN])
                ->latest();

            if ($request->filled('tahun')) {
                $query->where('tahun', $request->integer('tahun'));
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
                        ->orWhereHas('dusun', function ($dusunQuery) use ($keyword): void {
                            $dusunQuery->where('nama_dusun', 'like', "%{$keyword}%");
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

    public function create(ElectreCalculation $electreCalculation): View|RedirectResponse
    {
        $electreCalculation->load(['results.dusun', 'keputusanAkhir']);

        if ($electreCalculation->status !== ElectreCalculation::STATUS_SELESAI) {
            return redirect()
                ->route('kepala-desa.hasil-rekomendasi.index')
                ->with('error', 'Perhitungan ELECTRE belum selesai. Kode Error: KEPUTUSAN_AKHIR_INVALID_CALCULATION');
        }

        if ($electreCalculation->keputusanAkhir) {
            return redirect()
                ->route('kepala-desa.keputusan-akhir.show', $electreCalculation->keputusanAkhir)
                ->with('error', 'Keputusan akhir untuk perhitungan ini sudah dibuat. Kode Error: KEPUTUSAN_AKHIR_DUPLICATE');
        }

        return view('kepala-desa.keputusan-akhir.create', [
            'calculation' => $electreCalculation,
            'results' => $electreCalculation->results->sortBy('ranking')->values(),
        ]);
    }

    public function store(StoreKeputusanAkhirRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $calculation = ElectreCalculation::with('keputusanAkhir')->findOrFail($data['electre_calculation_id']);

            if ($calculation->status !== ElectreCalculation::STATUS_SELESAI) {
                return back()
                    ->withInput()
                    ->with('error', 'Perhitungan ELECTRE belum selesai. Kode Error: KEPUTUSAN_AKHIR_INVALID_CALCULATION');
            }

            if ($calculation->keputusanAkhir) {
                return redirect()
                    ->route('kepala-desa.keputusan-akhir.show', $calculation->keputusanAkhir)
                    ->with('error', 'Keputusan akhir untuk perhitungan ini sudah dibuat. Kode Error: KEPUTUSAN_AKHIR_DUPLICATE');
            }

            $result = ElectreResult::where('electre_calculation_id', $calculation->id)
                ->where('dusun_id', $data['dusun_id'])
                ->first();

            if (! $result) {
                return back()
                    ->withInput()
                    ->with('error', 'Dusun yang dipilih tidak terdapat pada hasil rekomendasi ini. Kode Error: KEPUTUSAN_AKHIR_INVALID_DUSUN');
            }

            $payload = [
                'electre_calculation_id' => $calculation->id,
                'electre_result_id' => $result->id,
                'dusun_id' => $result->dusun_id,
                'ditetapkan_oleh' => $request->user()->id,
                'nomor_keputusan' => $data['nomor_keputusan'] ?? null,
                'tanggal_keputusan' => $data['tanggal_keputusan'],
                'tahun' => $calculation->tahun,
                'status' => $data['status'],
                'dasar_pertimbangan' => $data['dasar_pertimbangan'] ?? null,
                'catatan_keputusan' => $data['catatan_keputusan'] ?? null,
            ];

            if (Schema::hasColumn('keputusan_akhirs', 'decided_by')) {
                $payload['decided_by'] = $request->user()->id;
            }

            if (Schema::hasColumn('keputusan_akhirs', 'catatan')) {
                $payload['catatan'] = $data['catatan_keputusan'] ?? null;
            }

            if (Schema::hasColumn('keputusan_akhirs', 'decided_at')) {
                $payload['decided_at'] = $data['status'] === KeputusanAkhir::STATUS_DITETAPKAN ? now() : null;
            }

            $keputusan = KeputusanAkhir::create($payload);

            Log::info('[KEPUTUSAN_AKHIR_CREATED] Keputusan akhir berhasil disimpan', [
                'user_id' => $request->user()->id,
                'keputusan_id' => $keputusan->id,
                'calculation_id' => $calculation->id,
                'dusun_id' => $keputusan->dusun_id,
                'status' => $keputusan->status,
            ]);

            return redirect()
                ->route('kepala-desa.keputusan-akhir.show', $keputusan)
                ->with('success', 'Keputusan akhir berhasil disimpan.');
        } catch (Throwable $e) {
            Log::error('[KEPUTUSAN_AKHIR_STORE_FAILED] Gagal menyimpan keputusan akhir', [
                'user_id' => $request->user()?->id,
                'calculation_id' => $request->input('electre_calculation_id'),
                'dusun_id' => $request->input('dusun_id'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan keputusan akhir. Kode Error: KEPUTUSAN_AKHIR_STORE_FAILED');
        }
    }

    public function show(KeputusanAkhir $keputusanAkhir): View
    {
        $keputusanAkhir->load(['calculation.results.dusun', 'dusun', 'decider', 'penetap', 'result']);

        return view('kepala-desa.keputusan-akhir.show', [
            'keputusan' => $keputusanAkhir,
            'calculation' => $keputusanAkhir->calculation,
            'results' => $keputusanAkhir->calculation?->results?->sortBy('ranking')->values() ?? collect(),
        ]);
    }
}
