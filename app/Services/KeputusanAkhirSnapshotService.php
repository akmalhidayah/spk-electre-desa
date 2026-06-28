<?php

namespace App\Services;

use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\Kriteria;
use App\Models\UsulanPembangunan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;

class KeputusanAkhirSnapshotService
{
    public function __construct(private readonly PejabatDesaService $pejabatDesaService) {}

    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(KeputusanAkhir $keputusan): array
    {
        $keputusan->loadMissing([
            'calculation.results.dusun',
            'calculation.details',
            'calculation.calculator',
            'dusun',
            'result.dusun',
            'penetap',
            'decider',
        ]);

        $calculation = $keputusan->calculation;
        $rankingSummary = collect($calculation?->details?->firstWhere('tahap', 'ranking_summary')?->data ?? [])
            ->keyBy('dusun_id');
        $results = $calculation?->results?->sortBy('ranking')->values() ?? collect();
        $selectedResult = $keputusan->result ?? $results->firstWhere('dusun_id', $keputusan->dusun_id);
        $snapshottedAt = now();

        return [
            'keputusan' => [
                'id' => $keputusan->id,
                'nomor_keputusan' => $keputusan->nomor_keputusan,
                'tanggal_keputusan' => $keputusan->tanggal_keputusan?->toDateString(),
                'tahun' => $keputusan->tahun ?? $calculation?->tahun,
                'status' => $keputusan->status,
                'dasar_pertimbangan' => $keputusan->dasar_pertimbangan,
                'catatan_keputusan' => $keputusan->catatan_keputusan,
                'tanda_tangan' => $keputusan->tanda_tangan,
                'ditetapkan_oleh' => $keputusan->ditetapkan_oleh,
                'decided_by' => $keputusan->decided_by,
                'decided_at' => $keputusan->decided_at?->toDateTimeString(),
                'snapshotted_at' => $snapshottedAt->toDateTimeString(),
            ],
            'kepala_desa' => [
                'nama' => $this->pejabatDesaService->kepalaDesaName() ?? $keputusan->penetap?->name ?? $keputusan->decider?->name,
            ],
            'penetap' => [
                'id' => $keputusan->penetap?->id,
                'name' => $keputusan->penetap?->name,
                'email' => $keputusan->penetap?->email,
            ],
            'calculation' => [
                'id' => $calculation?->id,
                'kode_perhitungan' => $calculation?->kode_perhitungan,
                'judul' => $calculation?->judul,
                'tahun' => $calculation?->tahun,
                'versi' => $calculation?->versi,
                'status' => $calculation?->status,
                'calculated_at' => $calculation?->calculated_at?->toDateTimeString(),
                'calculated_by' => $calculation?->calculator?->name,
            ],
            'selected_result' => $this->resultSnapshot($selectedResult, $rankingSummary),
            'results' => $results
                ->map(fn (ElectreResult $result): array => $this->resultSnapshot($result, $rankingSummary))
                ->values()
                ->all(),
            'kriterias' => Kriteria::aktif()
                ->ordered()
                ->get()
                ->map(fn (Kriteria $kriteria): array => [
                    'id' => $kriteria->id,
                    'kode_kriteria' => $kriteria->kode,
                    'kode' => $kriteria->kode,
                    'nama_kriteria' => $kriteria->nama_kriteria,
                    'bobot' => $kriteria->bobot,
                    'tipe' => $kriteria->tipe ?: Kriteria::TIPE_BENEFIT,
                    'satuan' => $kriteria->satuan ?? null,
                    'urutan' => $kriteria->urutan,
                ])
                ->values()
                ->all(),
            'accepted_usulans' => $this->acceptedUsulans((int) ($keputusan->tahun ?? $calculation?->tahun ?? now()->year))
                ->map(fn (UsulanPembangunan $usulan): array => [
                    'id' => $usulan->id,
                    'dusun_id' => $usulan->dusun_id,
                    'nama_dusun' => $usulan->dusun?->nama_dusun,
                    'nama_kegiatan' => $usulan->nama_kegiatan,
                    'lokasi_kegiatan' => $usulan->lokasi_kegiatan,
                    'prakiraan_volume' => $usulan->prakiraan_volume,
                    'satuan' => $usulan->satuan,
                    'jumlah_usulan' => $usulan->jumlah_usulan,
                    'estimasi_anggaran' => $usulan->estimasi_anggaran,
                    'penerima_manfaat_laki_laki' => $usulan->penerima_manfaat_lk,
                    'penerima_manfaat_perempuan' => $usulan->penerima_manfaat_pr,
                    'penerima_manfaat_rtm' => $usulan->penerima_manfaat_a_rtm,
                    'penerima_manfaat_lk' => $usulan->penerima_manfaat_lk,
                    'penerima_manfaat_pr' => $usulan->penerima_manfaat_pr,
                    'penerima_manfaat_a_rtm' => $usulan->penerima_manfaat_a_rtm,
                    'kategori_kegiatan' => $usulan->kategori_kegiatan,
                    'sdgs_ke' => $usulan->sdgs_ke,
                    'status' => $usulan->status,
                    'status_prioritas' => $usulan->status_prioritas,
                    'deskripsi' => $usulan->deskripsi,
                ])
                ->values()
                ->all(),
        ];
    }

    public function saveSnapshot(KeputusanAkhir $keputusan): KeputusanAkhir
    {
        if ($keputusan->status !== KeputusanAkhir::STATUS_DITETAPKAN || $keputusan->snapshot_data) {
            return $keputusan;
        }

        $snapshot = $this->buildSnapshot($keputusan);

        $keputusan->forceFill([
            'snapshot_data' => $snapshot,
            'snapshotted_at' => Carbon::parse($snapshot['keputusan']['snapshotted_at']),
        ])->save();

        return $keputusan->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function pdfViewData(KeputusanAkhir $keputusan): array
    {
        $snapshot = $keputusan->status === KeputusanAkhir::STATUS_DITETAPKAN
            ? ($keputusan->snapshot_data ?: $this->saveSnapshot($keputusan)->snapshot_data)
            : $this->buildSnapshot($keputusan);

        return $this->snapshotToViewData($snapshot, $keputusan->status !== KeputusanAkhir::STATUS_DITETAPKAN);
    }

    public function storePdfFromSnapshot(KeputusanAkhir $keputusan): KeputusanAkhir
    {
        if ($keputusan->status !== KeputusanAkhir::STATUS_DITETAPKAN) {
            return $keputusan;
        }

        $keputusan = $this->saveSnapshot($keputusan);

        if ($keputusan->pdf_path && Storage::disk('public')->exists($keputusan->pdf_path)) {
            if (! $keputusan->pdf_hash) {
                $keputusan->forceFill([
                    'pdf_hash' => hash('sha256', Storage::disk('public')->get($keputusan->pdf_path)),
                ])->save();

                return $keputusan->refresh();
            }

            return $keputusan;
        }

        $data = $this->pdfViewData($keputusan);
        $pdfBinary = Pdf::loadView('pdf.keputusan-akhir', $data)
            ->setPaper('a4', 'portrait')
            ->output();
        $tahun = (int) data_get($keputusan->snapshot_data, 'keputusan.tahun', $keputusan->tahun ?? now()->year);
        $path = "keputusan-akhir/{$tahun}/keputusan-akhir-{$keputusan->id}.pdf";

        Storage::disk('public')->put($path, $pdfBinary);

        $keputusan->forceFill([
            'pdf_path' => $path,
            'pdf_hash' => hash('sha256', $pdfBinary),
        ])->save();

        return $keputusan->refresh();
    }

    public function pdfStoragePath(KeputusanAkhir $keputusan): ?string
    {
        if (! $keputusan->pdf_path || ! Storage::disk('public')->exists($keputusan->pdf_path)) {
            return null;
        }

        return Storage::disk('public')->path($keputusan->pdf_path);
    }

    /**
     * @return Collection<int, UsulanPembangunan>
     */
    private function acceptedUsulans(int $tahun): Collection
    {
        return UsulanPembangunan::with(['dusun', 'dusunsTerkait'])
            ->tahun($tahun)
            ->diterimaAtauPrioritas()
            ->orderBy('dusun_id')
            ->orderBy('nama_kegiatan')
            ->get();
    }

    /**
     * @param  Collection<string|int, mixed>  $rankingSummary
     * @return array<string, mixed>
     */
    private function resultSnapshot(?ElectreResult $result, Collection $rankingSummary): array
    {
        $summary = $result ? $rankingSummary->get($result->dusun_id, []) : [];

        return [
            'id' => $result?->id,
            'dusun_id' => $result?->dusun_id,
            'kode_alternatif' => $result?->dusun?->kode_alternatif,
            'nama_dusun' => $result?->dusun?->nama_dusun,
            'ranking' => $result?->ranking,
            'skor_dominasi' => $result?->skor_dominasi,
            'total_terbobot' => data_get($summary, 'total_terbobot'),
            'status_prioritas' => $result?->status_prioritas,
            'keterangan' => $result?->keterangan,
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function snapshotToViewData(array $snapshot, bool $draft = false): array
    {
        $selected = $snapshot['selected_result'] ?? [];
        $keputusan = new Fluent($snapshot['keputusan'] ?? []);
        $keputusan->tanggal_keputusan = $this->dateOrNull($keputusan->tanggal_keputusan);
        $keputusan->decided_at = $this->datetimeOrNull($keputusan->decided_at);
        $keputusan->snapshotted_at = $this->datetimeOrNull($keputusan->snapshotted_at);
        $keputusan->dusun_id = $selected['dusun_id'] ?? null;
        $keputusan->dusun = new Fluent([
            'id' => $selected['dusun_id'] ?? null,
            'kode_alternatif' => $selected['kode_alternatif'] ?? null,
            'nama_dusun' => $selected['nama_dusun'] ?? null,
        ]);
        $keputusan->penetap = new Fluent($snapshot['penetap'] ?? []);
        $keputusan->decider = new Fluent($snapshot['penetap'] ?? []);

        $calculation = new Fluent($snapshot['calculation'] ?? []);
        $calculation->calculated_at = $this->datetimeOrNull($calculation->calculated_at);
        $calculation->total_alternatif = count($snapshot['results'] ?? []);
        $calculation->total_kriteria = count($snapshot['kriterias'] ?? []);

        return [
            'keputusan' => $keputusan,
            'calculation' => $calculation,
            'results' => collect($snapshot['results'] ?? [])->map(fn (array $result): Fluent => $this->resultObject($result)),
            'kriterias' => collect($snapshot['kriterias'] ?? [])->map(fn (array $kriteria): Fluent => new Fluent($kriteria)),
            'acceptedUsulans' => collect($snapshot['accepted_usulans'] ?? [])->map(fn (array $usulan): Fluent => $this->usulanObject($usulan)),
            'kepalaDesaName' => data_get($snapshot, 'kepala_desa.nama'),
            'isSnapshot' => ! $draft,
            'isDraftPdf' => $draft,
            'snapshotLabel' => $draft
                ? 'DRAFT - dokumen pratinjau dan belum menjadi arsip final.'
                : 'Dokumen final berdasarkan snapshot keputusan pada '.($snapshot['keputusan']['snapshotted_at'] ?? '-'),
        ];
    }

    private function resultObject(array $result): Fluent
    {
        $object = new Fluent($result);
        $object->dusun = new Fluent([
            'id' => $result['dusun_id'] ?? null,
            'kode_alternatif' => $result['kode_alternatif'] ?? null,
            'nama_dusun' => $result['nama_dusun'] ?? null,
        ]);

        return $object;
    }

    private function usulanObject(array $usulan): Fluent
    {
        $object = new Fluent($usulan);
        $object->dusun = new Fluent([
            'id' => $usulan['dusun_id'] ?? null,
            'nama_dusun' => $usulan['nama_dusun'] ?? null,
        ]);

        return $object;
    }

    private function dateOrNull(mixed $value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    private function datetimeOrNull(mixed $value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }
}
