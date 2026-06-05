<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKriteriaRequest;
use App\Http\Requests\UpdateKriteriaRequest;
use App\Models\Kriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class KriteriaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $kriterias = Kriteria::query()
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('kode', 'like', "%{$keyword}%")
                            ->orWhere('nama_kriteria', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('status'), function ($query) use ($request): void {
                    $query->where('status', $request->string('status')->toString());
                })
                ->when($request->filled('tipe'), function ($query) use ($request): void {
                    $query->where('tipe', $request->string('tipe')->toString());
                })
                ->ordered()
                ->paginate(10)
                ->withQueryString();

            $totalBobotAktif = (float) Kriteria::aktif()->sum('bobot');

            return view('admin.kriterias.index', [
                'kriterias' => $kriterias,
                'totalKriteria' => Kriteria::count(),
                'totalKriteriaAktif' => Kriteria::aktif()->count(),
                'totalKriteriaNonaktif' => Kriteria::nonaktif()->count(),
                'totalBobotAktif' => $totalBobotAktif,
                'statusBobotAktif' => $this->weightStatus($totalBobotAktif),
                'filters' => [
                    'q' => $request->string('q')->toString(),
                    'status' => $request->string('status')->toString(),
                    'tipe' => $request->string('tipe')->toString(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[KRITERIA_INDEX_FAILED] Gagal memuat data kriteria', [
                'error_code' => 'KRITERIA_INDEX_FAILED',
                'user_id' => auth()->id(),
                'filters' => $request->only(['q', 'status', 'tipe']),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data kriteria. Silakan coba kembali. Kode Error: KRITERIA_INDEX_FAILED');
        }
    }

    public function create(): View
    {
        return view('admin.kriterias.create', [
            'kriteria' => new Kriteria([
                'tipe' => Kriteria::TIPE_BENEFIT,
                'status' => Kriteria::STATUS_AKTIF,
                'urutan' => ((int) Kriteria::max('urutan')) + 1,
            ]),
        ]);
    }

    public function store(StoreKriteriaRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($data['status'] === Kriteria::STATUS_AKTIF) {
                $totalBobotBaru = (float) Kriteria::aktif()->sum('bobot') + (float) $data['bobot'];

                if ($totalBobotBaru > 100) {
                    Log::warning('[KRITERIA_WEIGHT_EXCEEDED] Total bobot kriteria aktif melebihi 100 persen saat tambah', [
                        'error_code' => 'KRITERIA_WEIGHT_EXCEEDED',
                        'user_id' => auth()->id(),
                        'total_bobot_baru' => $totalBobotBaru,
                    ]);

                    return back()
                        ->withInput()
                        ->with('error', 'Total bobot kriteria aktif tidak boleh melebihi 100%. Kode Error: KRITERIA_WEIGHT_EXCEEDED');
                }
            }

            $kriteria = Kriteria::create($data);

            Log::info('[KRITERIA_CREATED] Data kriteria berhasil dibuat', [
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'kode' => $kriteria->kode,
                'nama_kriteria' => $kriteria->nama_kriteria,
            ]);

            return redirect()
                ->route('admin.kriterias.index')
                ->with('success', 'Data kriteria berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('[KRITERIA_STORE_FAILED] Gagal menyimpan data kriteria', [
                'error_code' => 'KRITERIA_STORE_FAILED',
                'user_id' => auth()->id(),
                'request' => $request->safe()->except(['_token']),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data kriteria. Silakan coba kembali. Kode Error: KRITERIA_STORE_FAILED');
        }
    }

    public function edit(Kriteria $kriteria): View
    {
        return view('admin.kriterias.edit', [
            'kriteria' => $kriteria,
        ]);
    }

    public function update(UpdateKriteriaRequest $request, Kriteria $kriteria): RedirectResponse
    {
        try {
            $data = $request->validated();
            $totalBobotAktifSelainIni = (float) Kriteria::aktif()
                ->where('id', '!=', $kriteria->id)
                ->sum('bobot');

            $totalBaru = $data['status'] === Kriteria::STATUS_AKTIF
                ? $totalBobotAktifSelainIni + (float) $data['bobot']
                : $totalBobotAktifSelainIni;

            if ($totalBaru > 100) {
                Log::warning('[KRITERIA_WEIGHT_EXCEEDED] Total bobot kriteria aktif melebihi 100 persen saat update', [
                    'error_code' => 'KRITERIA_WEIGHT_EXCEEDED',
                    'user_id' => auth()->id(),
                    'kriteria_id' => $kriteria->id,
                    'total_bobot_baru' => $totalBaru,
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Total bobot kriteria aktif tidak boleh melebihi 100%. Kode Error: KRITERIA_WEIGHT_EXCEEDED');
            }

            $kriteria->update($data);

            Log::info('[KRITERIA_UPDATED] Data kriteria berhasil diperbarui', [
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'kode' => $kriteria->kode,
                'nama_kriteria' => $kriteria->nama_kriteria,
            ]);

            return redirect()
                ->route('admin.kriterias.index')
                ->with('success', 'Data kriteria berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[KRITERIA_UPDATE_FAILED] Gagal memperbarui data kriteria', [
                'error_code' => 'KRITERIA_UPDATE_FAILED',
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'request' => $request->safe()->except(['_token', '_method']),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data kriteria. Silakan coba kembali. Kode Error: KRITERIA_UPDATE_FAILED');
        }
    }

    public function toggleStatus(Kriteria $kriteria): RedirectResponse
    {
        try {
            if ($kriteria->status === Kriteria::STATUS_NONAKTIF) {
                $totalBobotBaru = (float) Kriteria::aktif()->sum('bobot') + (float) $kriteria->bobot;

                if ($totalBobotBaru > 100) {
                    Log::warning('[KRITERIA_WEIGHT_EXCEEDED] Total bobot kriteria aktif melebihi 100 persen saat toggle', [
                        'error_code' => 'KRITERIA_WEIGHT_EXCEEDED',
                        'user_id' => auth()->id(),
                        'kriteria_id' => $kriteria->id,
                        'total_bobot_baru' => $totalBobotBaru,
                    ]);

                    return back()->with('error', 'Total bobot kriteria aktif tidak boleh melebihi 100%. Kode Error: KRITERIA_WEIGHT_EXCEEDED');
                }
            }

            $statusBaru = $kriteria->status === Kriteria::STATUS_AKTIF
                ? Kriteria::STATUS_NONAKTIF
                : Kriteria::STATUS_AKTIF;

            $kriteria->update(['status' => $statusBaru]);

            Log::info('[KRITERIA_STATUS_TOGGLED] Status kriteria berhasil diubah', [
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'status' => $kriteria->status,
            ]);

            $totalBobotAktif = (float) Kriteria::aktif()->sum('bobot');
            $message = 'Status kriteria berhasil diperbarui.';

            if ($totalBobotAktif < 100) {
                $message .= ' Total bobot aktif belum mencapai 100%. Perhitungan ELECTRE belum ideal.';
            }

            return back()->with('success', $message);
        } catch (Throwable $e) {
            Log::error('[KRITERIA_TOGGLE_STATUS_FAILED] Gagal mengubah status kriteria', [
                'error_code' => 'KRITERIA_TOGGLE_STATUS_FAILED',
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengubah status kriteria. Silakan coba kembali. Kode Error: KRITERIA_TOGGLE_STATUS_FAILED');
        }
    }

    public function destroy(Kriteria $kriteria): RedirectResponse
    {
        try {
            if ($kriteria->penilaianAlternatifs()->exists()) {
                Log::warning('[KRITERIA_DELETE_BLOCKED] Kriteria sudah digunakan dalam penilaian alternatif', [
                    'error_code' => 'KRITERIA_DELETE_BLOCKED',
                    'user_id' => auth()->id(),
                    'kriteria_id' => $kriteria->id,
                    'kode' => $kriteria->kode,
                ]);

                return back()->with('error', 'Kriteria sudah digunakan dalam penilaian alternatif, sehingga tidak dapat dihapus. Silakan nonaktifkan kriteria. Kode Error: KRITERIA_DELETE_BLOCKED');
            }

            $kriteria->delete();

            Log::info('[KRITERIA_DELETED] Data kriteria berhasil dihapus soft delete', [
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'kode' => $kriteria->kode,
                'nama_kriteria' => $kriteria->nama_kriteria,
            ]);

            return redirect()
                ->route('admin.kriterias.index')
                ->with('success', 'Data kriteria berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[KRITERIA_DELETE_FAILED] Gagal menghapus data kriteria', [
                'error_code' => 'KRITERIA_DELETE_FAILED',
                'user_id' => auth()->id(),
                'kriteria_id' => $kriteria->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data kriteria. Silakan coba kembali. Kode Error: KRITERIA_DELETE_FAILED');
        }
    }

    private function weightStatus(float $totalBobotAktif): string
    {
        if (abs($totalBobotAktif - 100.0) < 0.001) {
            return 'valid';
        }

        return $totalBobotAktif < 100 ? 'kurang' : 'lebih';
    }
}
