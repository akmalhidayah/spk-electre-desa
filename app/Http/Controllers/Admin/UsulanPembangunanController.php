<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsulanPembangunanRequest;
use App\Http\Requests\UpdateUsulanPembangunanRequest;
use App\Models\Dusun;
use App\Models\TahunPerencanaan;
use App\Models\UsulanPembangunan;
use App\Services\PejabatDesaService;
use App\Services\RecalculationFlagService;
use App\Services\TahunAktifService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UsulanPembangunanController extends Controller
{
    public function index(Request $request, TahunAktifService $tahunAktifService): View|RedirectResponse
    {
        try {
            $tahun = $tahunAktifService->resolveYear($request->filled('tahun') ? $request->integer('tahun') : null);

            $usulans = UsulanPembangunan::with(['dusun', 'dusunsTerkait', 'pengaju'])
                ->tahun($tahun)
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('nama_kegiatan', 'like', "%{$keyword}%")
                            ->orWhere('deskripsi', 'like', "%{$keyword}%")
                            ->orWhereHas('dusun', function ($query) use ($keyword): void {
                                $query->where('nama_dusun', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->when($request->filled('dusun_id'), function ($query) use ($request): void {
                    $query->where(function ($query) use ($request): void {
                        $dusunId = $request->integer('dusun_id');

                        $query->where('dusun_id', $dusunId)
                            ->orWhereHas('dusunsTerkait', fn ($query) => $query->where('dusuns.id', $dusunId));
                    });
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                })
                ->when($request->filled('tipe_usulan'), function ($query) use ($request): void {
                    $query->where('tipe_usulan', $request->string('tipe_usulan')->toString());
                })
                ->when($request->filled('kategori_kegiatan'), function ($query) use ($request): void {
                    $query->where('kategori_kegiatan', $request->string('kategori_kegiatan')->toString());
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('admin.usulan.index', [
                'usulans' => $usulans,
                'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
                'statuses' => UsulanPembangunan::STATUSES,
                'tipeUsulans' => UsulanPembangunan::TIPE_USULANS,
                'tahunTersedia' => TahunPerencanaan::orderByDesc('tahun')->pluck('tahun'),
                'kategoriTersedia' => UsulanPembangunan::tahun($tahun)->whereNotNull('kategori_kegiatan')->distinct()->orderBy('kategori_kegiatan')->pluck('kategori_kegiatan'),
                'periode' => TahunPerencanaan::where('tahun', $tahun)->first(),
                'stats' => $this->stats($tahun),
                'acceptedUsulansForPdf' => UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
                    ->tahun($tahun)
                    ->diterima()
                    ->orderBy('dusun_id')
                    ->orderBy('nama_kegiatan')
                    ->get(),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => (string) $tahun,
                    'dusun_id' => $request->string('dusun_id')->toString(),
                    'status' => $request->string('status')->toString(),
                    'tipe_usulan' => $request->string('tipe_usulan')->toString(),
                    'kategori_kegiatan' => $request->string('kategori_kegiatan')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[USULAN_INDEX_FAILED] Gagal memuat data usulan pembangunan', $this->logContext($e, $request));

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data usulan. Silakan coba kembali. Kode Error: USULAN_INDEX_FAILED');
        }
    }

    public function create(TahunAktifService $tahunAktifService): View
    {
        return view('admin.usulan.create', [
            'usulan' => new UsulanPembangunan([
                'tahun' => $tahunAktifService->getActiveYear(),
                'tipe_usulan' => UsulanPembangunan::TIPE_DUSUN,
                'status' => UsulanPembangunan::STATUS_DIAJUKAN,
                'status_prioritas' => UsulanPembangunan::PRIORITAS_NON_PRIORITAS,
            ]),
            'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
            'statuses' => UsulanPembangunan::STATUSES,
            'tipeUsulans' => UsulanPembangunan::TIPE_USULANS,
        ]);
    }

    public function store(StoreUsulanPembangunanRequest $request, RecalculationFlagService $recalculationFlagService): RedirectResponse
    {
        try {
            $data = $request->validated();
            $dusunTerkaitIds = $this->normalizeDusunTerkaitIds($data);
            unset($data['dusun_terkait_ids']);

            $data['user_id'] = $request->user()->id;
            $data['status'] = $data['status'] ?? UsulanPembangunan::STATUS_DIAJUKAN;
            $data['status_prioritas'] = $data['status_prioritas'] ?? UsulanPembangunan::PRIORITAS_NON_PRIORITAS;
            $data['is_data_pendukung_penilaian'] = $data['status'] === UsulanPembangunan::STATUS_DITERIMA
                && $data['tipe_usulan'] !== UsulanPembangunan::TIPE_UMUM_DESA;

            if ($data['tipe_usulan'] === UsulanPembangunan::TIPE_UMUM_DESA) {
                $data['dusun_id'] = null;
            }

            $usulan = UsulanPembangunan::create($data);
            $usulan->dusunsTerkait()->sync($dusunTerkaitIds);

            if ($usulan->is_data_pendukung_penilaian) {
                $recalculationFlagService->mark((int) $usulan->tahun, 'Ada usulan diterima atau diperbarui.');
            }

            Log::info('[USULAN_CREATED] Data usulan berhasil dibuat', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $usulan->dusun_id,
                'usulan_id' => $usulan->id,
            ]);

            return redirect()
                ->route('admin.usulan.index')
                ->with('success', 'Data usulan pembangunan berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('[USULAN_STORE_FAILED] Gagal menyimpan usulan pembangunan', $this->logContext($e, $request));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan usulan. Silakan coba kembali. Kode Error: USULAN_STORE_FAILED');
        }
    }

    public function edit(UsulanPembangunan $usulanPembangunan): View
    {
        return view('admin.usulan.edit', [
            'usulan' => $usulanPembangunan->load(['dusun', 'dusunsTerkait', 'pengaju']),
            'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
            'statuses' => UsulanPembangunan::STATUSES,
            'tipeUsulans' => UsulanPembangunan::TIPE_USULANS,
        ]);
    }

    public function update(UpdateUsulanPembangunanRequest $request, UsulanPembangunan $usulanPembangunan, RecalculationFlagService $recalculationFlagService): RedirectResponse
    {
        try {
            $wasSupportingData = (bool) $usulanPembangunan->is_data_pendukung_penilaian;
            $data = $request->validated();
            $dusunTerkaitIds = $this->normalizeDusunTerkaitIds($data);
            unset($data['dusun_terkait_ids']);

            $data['status'] = $data['status'] ?? $usulanPembangunan->status;
            $data['status_prioritas'] = $data['status_prioritas'] ?? $usulanPembangunan->status_prioritas;
            $data['is_data_pendukung_penilaian'] = $data['status'] === UsulanPembangunan::STATUS_DITERIMA
                && $data['tipe_usulan'] !== UsulanPembangunan::TIPE_UMUM_DESA;

            if ($data['tipe_usulan'] === UsulanPembangunan::TIPE_UMUM_DESA) {
                $data['dusun_id'] = null;
            }

            $usulanPembangunan->update($data);
            $usulanPembangunan->dusunsTerkait()->sync($dusunTerkaitIds);

            if ($wasSupportingData || $usulanPembangunan->is_data_pendukung_penilaian) {
                $recalculationFlagService->mark((int) $usulanPembangunan->tahun, 'Ada usulan diterima atau diperbarui.');
            }

            Log::info('[USULAN_UPDATED] Data usulan berhasil diperbarui', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $usulanPembangunan->dusun_id,
                'usulan_id' => $usulanPembangunan->id,
            ]);

            return redirect()
                ->route('admin.usulan.index')
                ->with('success', 'Data usulan pembangunan berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[USULAN_UPDATE_FAILED] Gagal memperbarui usulan pembangunan', $this->logContext($e, $request, $usulanPembangunan));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui usulan. Silakan coba kembali. Kode Error: USULAN_UPDATE_FAILED');
        }
    }

    public function updateStatus(Request $request, UsulanPembangunan $usulanPembangunan, RecalculationFlagService $recalculationFlagService): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(UsulanPembangunan::STATUSES)],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        try {
            $wasSupportingData = (bool) $usulanPembangunan->is_data_pendukung_penilaian;
            $data['is_data_pendukung_penilaian'] = $data['status'] === UsulanPembangunan::STATUS_DITERIMA
                && $usulanPembangunan->tipe_usulan !== UsulanPembangunan::TIPE_UMUM_DESA;

            $usulanPembangunan->update($data);

            if ($wasSupportingData || $usulanPembangunan->is_data_pendukung_penilaian) {
                $recalculationFlagService->mark((int) $usulanPembangunan->tahun, 'Ada usulan diterima atau diperbarui.');
            }

            Log::info('[USULAN_STATUS_UPDATED] Status usulan berhasil diperbarui', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $usulanPembangunan->dusun_id,
                'usulan_id' => $usulanPembangunan->id,
                'status' => $usulanPembangunan->status,
            ]);

            return back()->with('success', 'Status usulan pembangunan berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[USULAN_STATUS_FAILED] Gagal memperbarui status usulan pembangunan', $this->logContext($e, $request, $usulanPembangunan));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui status usulan. Silakan coba kembali. Kode Error: USULAN_STATUS_FAILED');
        }
    }

    public function exportAcceptedPdf(Request $request, PejabatDesaService $pejabatDesaService)
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer'],
            'usulan_ids' => ['required', 'array', 'min:1'],
            'usulan_ids.*' => ['integer', 'distinct', 'exists:usulan_pembangunans,id'],
        ], [
            'usulan_ids.required' => 'Pilih minimal satu usulan diterima untuk dicetak.',
            'usulan_ids.min' => 'Pilih minimal satu usulan diterima untuk dicetak.',
        ]);

        try {
            $tahun = (int) $data['tahun'];
            $selectedIds = array_map('intval', $data['usulan_ids']);

            $usulans = UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
                ->whereIn('id', $selectedIds)
                ->tahun($tahun)
                ->diterima()
                ->orderBy('dusun_id')
                ->orderBy('nama_kegiatan')
                ->get();

            if ($usulans->count() !== count($selectedIds)) {
                return back()->with('error', 'Sebagian usulan tidak valid, bukan status diterima, atau bukan tahun yang dipilih.');
            }

            $pdfTitle = "Daftar Usulan Pembangunan Diterima Tahun {$tahun}";

            Log::info('[USULAN_ACCEPTED_PDF_CREATED] PDF daftar usulan diterima dibuat', [
                'user_id' => $request->user()?->id,
                'role' => $request->user()?->role,
                'tahun' => $tahun,
                'total_usulan' => $usulans->count(),
            ]);

            return Pdf::loadView('pdf.usulan-diterima', [
                'pdfTitle' => $pdfTitle,
                'tahun' => $tahun,
                'usulans' => $usulans,
                'kepalaDesaName' => $pejabatDesaService->kepalaDesaName(),
            ])
                ->setPaper('a4', 'landscape')
                ->stream('daftar-usulan-diterima-'.$tahun.'.pdf');
        } catch (Throwable $e) {
            Log::error('[USULAN_ACCEPTED_PDF_FAILED] Gagal membuat PDF daftar usulan diterima', $this->logContext($e, $request));

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF usulan diterima. Silakan coba kembali. Kode Error: USULAN_ACCEPTED_PDF_FAILED');
        }
    }

    public function destroy(Request $request, UsulanPembangunan $usulanPembangunan, RecalculationFlagService $recalculationFlagService): RedirectResponse
    {
        try {
            if ($usulanPembangunan->status === UsulanPembangunan::STATUS_MASUK_PRIORITAS) {
                Log::warning('[USULAN_DELETE_BLOCKED] Usulan masuk prioritas tidak dapat dihapus', [
                    'error_code' => 'USULAN_DELETE_BLOCKED',
                    'user_id' => $request->user()->id,
                    'role' => $request->user()->role,
                    'dusun_id' => $usulanPembangunan->dusun_id,
                    'usulan_id' => $usulanPembangunan->id,
                ]);

                return back()->with('error', 'Usulan yang sudah masuk prioritas tidak dapat dihapus. Kode Error: USULAN_DELETE_BLOCKED');
            }

            $wasSupportingData = (bool) $usulanPembangunan->is_data_pendukung_penilaian;
            $tahun = (int) $usulanPembangunan->tahun;
            $usulanPembangunan->delete();

            if ($wasSupportingData) {
                $recalculationFlagService->mark($tahun, 'Ada usulan diterima atau diperbarui.');
            }

            Log::info('[USULAN_DELETED] Data usulan berhasil dihapus soft delete', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $usulanPembangunan->dusun_id,
                'usulan_id' => $usulanPembangunan->id,
            ]);

            return redirect()
                ->route('admin.usulan.index')
                ->with('success', 'Data usulan pembangunan berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[USULAN_DELETE_FAILED] Gagal menghapus usulan pembangunan', $this->logContext($e, $request, $usulanPembangunan));

            return back()->with('error', 'Terjadi kesalahan saat menghapus usulan. Silakan coba kembali. Kode Error: USULAN_DELETE_FAILED');
        }
    }

    /**
     * @return array<string, int>
     */
    private function stats(int $tahun): array
    {
        $query = UsulanPembangunan::tahun($tahun);

        return [
            'total' => (clone $query)->count(),
            'umum_desa' => (clone $query)->tipe(UsulanPembangunan::TIPE_UMUM_DESA)->count(),
            'dusun' => (clone $query)->tipe(UsulanPembangunan::TIPE_DUSUN)->count(),
            'lintas_dusun' => (clone $query)->tipe(UsulanPembangunan::TIPE_LINTAS_DUSUN)->count(),
            'diajukan' => (clone $query)->diajukan()->count(),
            'diproses' => (clone $query)->diproses()->count(),
            'diterima' => (clone $query)->diterima()->count(),
            'ditolak' => (clone $query)->ditolak()->count(),
            'masuk_prioritas' => (clone $query)->masukPrioritas()->count(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, int>
     */
    private function normalizeDusunTerkaitIds(array $data): array
    {
        if ($data['tipe_usulan'] === UsulanPembangunan::TIPE_UMUM_DESA) {
            return [];
        }

        if ($data['tipe_usulan'] === UsulanPembangunan::TIPE_LINTAS_DUSUN) {
            return array_values(array_unique(array_map('intval', $data['dusun_terkait_ids'] ?? [])));
        }

        return empty($data['dusun_id']) ? [] : [(int) $data['dusun_id']];
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(Throwable $e, Request $request, ?UsulanPembangunan $usulan = null): array
    {
        return [
            'user_id' => $request->user()?->id,
            'role' => $request->user()?->role,
            'dusun_id' => $usulan?->dusun_id ?? $request->input('dusun_id'),
            'usulan_id' => $usulan?->id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}
