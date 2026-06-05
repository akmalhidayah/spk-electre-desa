<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsulanPembangunanRequest;
use App\Http\Requests\UpdateUsulanPembangunanRequest;
use App\Models\Dusun;
use App\Models\UsulanPembangunan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UsulanPembangunanController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $usulans = UsulanPembangunan::with(['dusun', 'pengaju'])
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
                ->when($request->filled('tahun'), function ($query) use ($request): void {
                    $query->where('tahun', $request->integer('tahun'));
                })
                ->when($request->filled('dusun_id'), function ($query) use ($request): void {
                    $query->where('dusun_id', $request->integer('dusun_id'));
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                })
                ->orderByDesc('tahun')
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('admin.usulan.index', [
                'usulans' => $usulans,
                'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
                'statuses' => UsulanPembangunan::STATUSES,
                'tahunTersedia' => UsulanPembangunan::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
                'stats' => $this->stats(),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => $request->string('tahun')->toString(),
                    'dusun_id' => $request->string('dusun_id')->toString(),
                    'status' => $request->string('status')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[USULAN_INDEX_FAILED] Gagal memuat data usulan pembangunan', $this->logContext($e, $request));

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data usulan. Silakan coba kembali. Kode Error: USULAN_INDEX_FAILED');
        }
    }

    public function create(): View
    {
        return view('admin.usulan.create', [
            'usulan' => new UsulanPembangunan([
                'tahun' => now()->year,
                'status' => UsulanPembangunan::STATUS_DIAJUKAN,
            ]),
            'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
            'statuses' => UsulanPembangunan::STATUSES,
        ]);
    }

    public function store(StoreUsulanPembangunanRequest $request): RedirectResponse
    {
        if (! $request->filled('dusun_id')) {
            return back()
                ->withInput()
                ->withErrors(['dusun_id' => 'Dusun wajib dipilih.']);
        }

        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['status'] = $data['status'] ?? UsulanPembangunan::STATUS_DIAJUKAN;

            $usulan = UsulanPembangunan::create($data);

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
            'usulan' => $usulanPembangunan->load(['dusun', 'pengaju']),
            'dusuns' => Dusun::aktif()->orderBy('nama_dusun')->get(),
            'statuses' => UsulanPembangunan::STATUSES,
        ]);
    }

    public function update(UpdateUsulanPembangunanRequest $request, UsulanPembangunan $usulanPembangunan): RedirectResponse
    {
        if (! $request->filled('dusun_id')) {
            return back()
                ->withInput()
                ->withErrors(['dusun_id' => 'Dusun wajib dipilih.']);
        }

        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? $usulanPembangunan->status;

            $usulanPembangunan->update($data);

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

    public function updateStatus(Request $request, UsulanPembangunan $usulanPembangunan): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(UsulanPembangunan::STATUSES)],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        try {
            $usulanPembangunan->update($data);

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

    public function destroy(Request $request, UsulanPembangunan $usulanPembangunan): RedirectResponse
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

            $usulanPembangunan->delete();

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
    private function stats(): array
    {
        return [
            'total' => UsulanPembangunan::count(),
            'diajukan' => UsulanPembangunan::diajukan()->count(),
            'diproses' => UsulanPembangunan::diproses()->count(),
            'diterima' => UsulanPembangunan::diterima()->count(),
            'ditolak' => UsulanPembangunan::ditolak()->count(),
            'masuk_prioritas' => UsulanPembangunan::masukPrioritas()->count(),
        ];
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
