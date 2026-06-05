<?php

namespace App\Http\Controllers\KepalaDusun;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsulanPembangunanRequest;
use App\Http\Requests\UpdateUsulanPembangunanRequest;
use App\Models\UsulanPembangunan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsulanPembangunanController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $user = $request->user();
            $baseQuery = UsulanPembangunan::with(['dusun', 'pengaju']);

            if ($user->dusun_id) {
                $baseQuery->where('dusun_id', $user->dusun_id);
            } else {
                $baseQuery->whereRaw('1 = 0');
            }

            $usulans = (clone $baseQuery)
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('nama_kegiatan', 'like', "%{$keyword}%")
                            ->orWhere('deskripsi', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('tahun'), function ($query) use ($request): void {
                    $query->where('tahun', $request->integer('tahun'));
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                })
                ->orderByDesc('tahun')
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('kepala-dusun.usulan.index', [
                'dusun' => $user->dusun,
                'usulans' => $usulans,
                'statuses' => UsulanPembangunan::STATUSES,
                'tahunTersedia' => (clone $baseQuery)->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
                'stats' => $this->stats($user->dusun_id),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'tahun' => $request->string('tahun')->toString(),
                    'status' => $request->string('status')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[USULAN_KEPALA_DUSUN_INDEX_FAILED] Gagal memuat data usulan kepala dusun', $this->logContext($e, $request));

            return redirect()
                ->route('kepala-dusun.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data usulan. Silakan coba kembali. Kode Error: USULAN_KEPALA_DUSUN_INDEX_FAILED');
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->dusun_id) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data dusun. Silakan hubungi admin.');
        }

        return view('kepala-dusun.usulan.create', [
            'dusun' => $request->user()->dusun,
            'usulan' => new UsulanPembangunan(['tahun' => now()->year]),
        ]);
    }

    public function store(StoreUsulanPembangunanRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->dusun_id) {
                return back()->with('error', 'Akun Anda belum terhubung dengan data dusun. Silakan hubungi admin.');
            }

            $data = $request->safe()->only(['tahun', 'nama_kegiatan', 'jumlah_usulan', 'estimasi_anggaran', 'deskripsi']);
            $data['dusun_id'] = $user->dusun_id;
            $data['user_id'] = $user->id;
            $data['status'] = UsulanPembangunan::STATUS_DIAJUKAN;
            $data['catatan_admin'] = null;

            $usulan = UsulanPembangunan::create($data);

            Log::info('[USULAN_KEPALA_DUSUN_CREATED] Usulan kepala dusun berhasil dibuat', [
                'user_id' => $user->id,
                'role' => $user->role,
                'dusun_id' => $user->dusun_id,
                'usulan_id' => $usulan->id,
            ]);

            return redirect()
                ->route('kepala-dusun.usulan.index')
                ->with('success', 'Usulan pembangunan berhasil diajukan.');
        } catch (Throwable $e) {
            Log::error('[USULAN_KEPALA_DUSUN_STORE_FAILED] Gagal menyimpan usulan kepala dusun', $this->logContext($e, $request));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan usulan. Silakan coba kembali. Kode Error: USULAN_KEPALA_DUSUN_STORE_FAILED');
        }
    }

    public function edit(Request $request, UsulanPembangunan $usulanPembangunan): View|RedirectResponse
    {
        $this->authorizeUsulan($request, $usulanPembangunan);

        if ($usulanPembangunan->status !== UsulanPembangunan::STATUS_DIAJUKAN) {
            return back()->with('error', 'Usulan hanya dapat diubah ketika status masih diajukan. Kode Error: USULAN_LOCKED');
        }

        return view('kepala-dusun.usulan.edit', [
            'dusun' => $request->user()->dusun,
            'usulan' => $usulanPembangunan,
        ]);
    }

    public function update(UpdateUsulanPembangunanRequest $request, UsulanPembangunan $usulanPembangunan): RedirectResponse
    {
        try {
            $this->authorizeUsulan($request, $usulanPembangunan);

            if ($usulanPembangunan->status !== UsulanPembangunan::STATUS_DIAJUKAN) {
                return back()->with('error', 'Usulan hanya dapat diubah ketika status masih diajukan. Kode Error: USULAN_LOCKED');
            }

            $usulanPembangunan->update($request->safe()->only([
                'tahun',
                'nama_kegiatan',
                'jumlah_usulan',
                'estimasi_anggaran',
                'deskripsi',
            ]));

            Log::info('[USULAN_KEPALA_DUSUN_UPDATED] Usulan kepala dusun berhasil diperbarui', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $request->user()->dusun_id,
                'usulan_id' => $usulanPembangunan->id,
            ]);

            return redirect()
                ->route('kepala-dusun.usulan.index')
                ->with('success', 'Usulan pembangunan berhasil diperbarui.');
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[USULAN_KEPALA_DUSUN_UPDATE_FAILED] Gagal memperbarui usulan kepala dusun', $this->logContext($e, $request, $usulanPembangunan));

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui usulan. Silakan coba kembali. Kode Error: USULAN_KEPALA_DUSUN_UPDATE_FAILED');
        }
    }

    public function destroy(Request $request, UsulanPembangunan $usulanPembangunan): RedirectResponse
    {
        try {
            $this->authorizeUsulan($request, $usulanPembangunan);

            if ($usulanPembangunan->status !== UsulanPembangunan::STATUS_DIAJUKAN) {
                return back()->with('error', 'Usulan hanya dapat dihapus ketika status masih diajukan. Kode Error: USULAN_LOCKED');
            }

            $usulanPembangunan->delete();

            Log::info('[USULAN_KEPALA_DUSUN_DELETED] Usulan kepala dusun berhasil dihapus', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $request->user()->dusun_id,
                'usulan_id' => $usulanPembangunan->id,
            ]);

            return redirect()
                ->route('kepala-dusun.usulan.index')
                ->with('success', 'Usulan pembangunan berhasil dihapus.');
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('[USULAN_KEPALA_DUSUN_DELETE_FAILED] Gagal menghapus usulan kepala dusun', $this->logContext($e, $request, $usulanPembangunan));

            return back()->with('error', 'Terjadi kesalahan saat menghapus usulan. Silakan coba kembali. Kode Error: USULAN_KEPALA_DUSUN_DELETE_FAILED');
        }
    }

    private function authorizeUsulan(Request $request, UsulanPembangunan $usulan): void
    {
        if ((int) $usulan->dusun_id !== (int) $request->user()->dusun_id) {
            Log::warning('[USULAN_FORBIDDEN] Kepala dusun mencoba mengakses usulan dusun lain', [
                'error_code' => 'USULAN_FORBIDDEN',
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'dusun_id' => $request->user()->dusun_id,
                'usulan_id' => $usulan->id,
            ]);

            abort(403, 'Kode Error: USULAN_FORBIDDEN');
        }
    }

    /**
     * @return array<string, int>
     */
    private function stats(?int $dusunId): array
    {
        $query = UsulanPembangunan::query();

        if ($dusunId) {
            $query->where('dusun_id', $dusunId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return [
            'total' => (clone $query)->count(),
            'diajukan' => (clone $query)->diajukan()->count(),
            'diproses' => (clone $query)->diproses()->count(),
            'diterima' => (clone $query)->diterima()->count(),
            'masuk_prioritas' => (clone $query)->masukPrioritas()->count(),
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
            'dusun_id' => $request->user()?->dusun_id,
            'usulan_id' => $usulan?->id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}
