<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDusunRequest;
use App\Http\Requests\UpdateDusunRequest;
use App\Models\Dusun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DusunController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $dusuns = Dusun::query()
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode_alternatif', 'like', "%{$keyword}%")
                            ->orWhere('nama_dusun', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                })
                ->orderBy('kode_alternatif')
                ->orderBy('nama_dusun')
                ->paginate(10)
                ->withQueryString();

            return view('admin.dusuns.index', [
                'dusuns' => $dusuns,
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'status' => $request->string('status')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[DUSUN_INDEX_FAILED] Gagal memuat data dusun', [
                'error_code' => 'DUSUN_INDEX_FAILED',
                'user_id' => $request->user()?->id,
                'filters' => $request->only(['q', 'status']),
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data dusun. Silakan coba kembali. Kode Error: DUSUN_INDEX_FAILED');
        }
    }

    public function create(): View
    {
        return view('admin.dusuns.create', [
            'dusun' => new Dusun(['status' => Dusun::STATUS_AKTIF]),
        ]);
    }

    public function store(StoreDusunRequest $request): RedirectResponse
    {
        try {
            $dusun = Dusun::create($request->validated());

            Log::info('[DUSUN_CREATED] Data dusun berhasil dibuat', [
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'kode_alternatif' => $dusun->kode_alternatif,
                'nama_dusun' => $dusun->nama_dusun,
            ]);

            return redirect()
                ->route('admin.dusuns.index')
                ->with('success', 'Data dusun berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('[DUSUN_STORE_FAILED] Gagal menyimpan data dusun', [
                'error_code' => 'DUSUN_STORE_FAILED',
                'user_id' => $request->user()?->id,
                'request' => $request->safe()->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data dusun. Silakan coba kembali. Kode Error: DUSUN_STORE_FAILED');
        }
    }

    public function edit(Dusun $dusun): View
    {
        return view('admin.dusuns.edit', [
            'dusun' => $dusun,
        ]);
    }

    public function update(UpdateDusunRequest $request, Dusun $dusun): RedirectResponse
    {
        try {
            $dusun->update($request->validated());

            Log::info('[DUSUN_UPDATED] Data dusun berhasil diperbarui', [
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'kode_alternatif' => $dusun->kode_alternatif,
                'nama_dusun' => $dusun->nama_dusun,
            ]);

            return redirect()
                ->route('admin.dusuns.index')
                ->with('success', 'Data dusun berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[DUSUN_UPDATE_FAILED] Gagal memperbarui data dusun', [
                'error_code' => 'DUSUN_UPDATE_FAILED',
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'request' => $request->safe()->except(['_token', '_method']),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data dusun. Silakan coba kembali. Kode Error: DUSUN_UPDATE_FAILED');
        }
    }

    public function toggleStatus(Request $request, Dusun $dusun): RedirectResponse
    {
        try {
            $dusun->update([
                'status' => $dusun->status === Dusun::STATUS_AKTIF
                    ? Dusun::STATUS_NONAKTIF
                    : Dusun::STATUS_AKTIF,
            ]);

            Log::info('[DUSUN_STATUS_TOGGLED] Status dusun berhasil diubah', [
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'status' => $dusun->status,
            ]);

            return back()->with('success', 'Status dusun berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[DUSUN_TOGGLE_STATUS_FAILED] Gagal mengubah status dusun', [
                'error_code' => 'DUSUN_TOGGLE_STATUS_FAILED',
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengubah status dusun. Silakan coba kembali. Kode Error: DUSUN_TOGGLE_STATUS_FAILED');
        }
    }

    public function destroy(Request $request, Dusun $dusun): RedirectResponse
    {
        try {
            if ($this->hasImportantRelations($dusun)) {
                return back()->with('error', 'Dusun sudah memiliki data terkait. Untuk menjaga histori, silakan nonaktifkan dusun ini. Kode Error: DUSUN_DELETE_BLOCKED');
            }

            $dusun->delete();

            Log::info('[DUSUN_DELETED] Data dusun berhasil dihapus soft delete', [
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'kode_alternatif' => $dusun->kode_alternatif,
                'nama_dusun' => $dusun->nama_dusun,
            ]);

            return redirect()
                ->route('admin.dusuns.index')
                ->with('success', 'Data dusun berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[DUSUN_DELETE_FAILED] Gagal menghapus data dusun', [
                'error_code' => 'DUSUN_DELETE_FAILED',
                'user_id' => $request->user()?->id,
                'dusun_id' => $dusun->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data dusun. Silakan coba kembali. Kode Error: DUSUN_DELETE_FAILED');
        }
    }

    private function hasImportantRelations(Dusun $dusun): bool
    {
        return $dusun->users()->exists()
            || $dusun->usulanPembangunans()->withTrashed()->exists()
            || $dusun->penilaianAlternatifs()->exists()
            || $dusun->electreResults()->exists();
    }
}
