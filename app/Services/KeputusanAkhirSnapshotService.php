<?php

namespace App\Services;

use App\Models\ElectreResult;
use App\Models\KeputusanAkhir;
use App\Models\UsulanPembangunan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;

class KeputusanAkhirSnapshotService
{
    public function __construct(private readonly PejabatDesaService $pejabatDesaService, private readonly BudgetAllocationService $budgetService) {}

    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(KeputusanAkhir $keputusan): array
    {
        $keputusan->loadMissing([
            'calculation.results.program.dusun',
            'calculation.results.program.dusunsTerkait',
            'calculation.tahunPerencanaan',
            'calculation.details',
            'calculation.calculator',
            'program.dusun',
            'program.dusunsTerkait',
            'result.program.dusun',
            'details.result.program.dusun',
            'penetap',
        ]);

        $calculation = $keputusan->calculation;
        $rankingSummary = collect($calculation?->details?->firstWhere('tahap', 'ranking_summary')?->data ?? [])
            ->keyBy('usulan_pembangunan_id');
        $results = $calculation?->results?->sortBy('ranking')->values() ?? collect();
        $selectedResult = $keputusan->result ?? $results->firstWhere('usulan_pembangunan_id', $keputusan->usulan_pembangunan_id);
        $selectedResults = $keputusan->details->isNotEmpty()
            ? $keputusan->details->map(fn ($detail): array => [
                'electre_result_id' => $detail->electre_result_id,
                'usulan_pembangunan_id' => $detail->usulan_pembangunan_id,
                'kode_alternatif' => $detail->kode_alternatif_snapshot,
                'nama_program' => $detail->nama_program_snapshot,
                'lokasi' => $detail->lokasi_snapshot,
                'nama_dusun' => $detail->nama_dusun_snapshot,
                'ranking' => $detail->ranking_snapshot,
                'skor_dominasi' => $detail->skor_dominasi_snapshot,
                'estimasi_anggaran' => $detail->estimasi_anggaran_snapshot !== null ? (float) $detail->estimasi_anggaran_snapshot : null,
            ])->values()
            : collect([$this->resultSnapshot($selectedResult, $rankingSummary)]);
        $budget = $this->budgetService->summary($keputusan->tahunPerencanaan);
        $decisionTotal = (float) $selectedResults->sum('estimasi_anggaran');
        $snapshottedAt = now();

        return [
            'keputusan' => [
                'id' => $keputusan->id,
                'nomor_keputusan' => $keputusan->nomor_keputusan,
                'tanggal_keputusan' => $keputusan->tanggal_keputusan?->toDateString(),
                'tahun' => $keputusan->tahunPerencanaan?->tahun ?? $calculation?->tahunPerencanaan?->tahun,
                'status' => $keputusan->status,
                'dasar_pertimbangan' => $keputusan->dasar_pertimbangan,
                'catatan_keputusan' => $keputusan->catatan_keputusan,
                'tanda_tangan' => $keputusan->tanda_tangan,
                'ditetapkan_oleh' => $keputusan->ditetapkan_oleh,
                'decided_at' => $keputusan->decided_at?->toDateTimeString(),
                'snapshotted_at' => $snapshottedAt->toDateTimeString(),
            ],
            'kepala_desa' => [
                'nama' => $this->pejabatDesaService->kepalaDesaName() ?? $keputusan->penetap?->name,
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
                'tahun' => $calculation?->tahunPerencanaan?->tahun,
                'versi' => $calculation?->versi,
                'status' => $calculation?->status,
                'calculated_at' => $calculation?->calculated_at?->toDateTimeString(),
                'calculated_by' => $calculation?->calculator?->name,
            ],
            'selected_result' => $this->resultSnapshot($selectedResult, $rankingSummary),
            'selected_results' => $selectedResults->all(),
            'budget_summary' => [
                'pagu_anggaran' => $budget['pagu'],
                'total_ditetapkan_sebelum_keputusan' => max($budget['total_ditetapkan'] - $decisionTotal, 0),
                'total_keputusan_ini' => $decisionTotal,
                'total_ditetapkan_setelah_keputusan' => $budget['total_ditetapkan'],
                'sisa_pagu_setelah_keputusan' => $budget['sisa_pagu'],
            ],
            'results' => $results
                ->map(fn (ElectreResult $result): array => $this->resultSnapshot($result, $rankingSummary))
                ->values()
                ->all(),
            'kriterias' => collect($calculation?->details?->firstWhere('tahap', 'metadata_kriteria')?->data ?? [])->all(),
            'accepted_usulans' => $this->acceptedUsulans((int) ($keputusan->tahunPerencanaan?->tahun ?? $calculation?->tahunPerencanaan?->tahun ?? now()->year))
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
                    'status_usulan' => $usulan->status_usulan,
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
        $tahun = (int) data_get($keputusan->snapshot_data, 'keputusan.tahun', $keputusan->tahunPerencanaan?->tahun ?? now()->year);
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
            ->diterima()
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
        $summary = $result ? $rankingSummary->get($result->usulan_pembangunan_id, []) : [];

        return [
            'id' => $result?->id,
            'usulan_pembangunan_id' => $result?->usulan_pembangunan_id,
            'kode_alternatif' => $result?->kode_alternatif,
            'nama_program' => $result?->nama_program_snapshot,
            'lokasi' => $result?->lokasi_snapshot,
            'nama_dusun' => $result?->nama_dusun_snapshot,
            'estimasi_anggaran' => data_get($summary, 'estimasi_anggaran', $result?->program?->estimasi_anggaran),
            'ranking' => $result?->ranking,
            'skor_dominasi' => $result?->skor_dominasi,
            'total_preferensi' => $result?->total_preferensi ?? data_get($summary, 'total_preferensi'),
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
        $keputusan->usulan_pembangunan_id = $selected['usulan_pembangunan_id'] ?? null;
        $keputusan->program = new Fluent([
            'id' => $selected['usulan_pembangunan_id'] ?? null,
            'nama_kegiatan' => $selected['nama_program'] ?? null,
            'lokasi_label' => $selected['lokasi'] ?? null,
            'estimasi_anggaran' => $selected['estimasi_anggaran'] ?? null,
        ]);
        $keputusan->selected_results = collect($snapshot['selected_results'] ?? [$selected])->map(fn (array $result): Fluent => $this->resultObject($result));
        $keputusan->budget_summary = new Fluent($snapshot['budget_summary'] ?? []);
        $keputusan->penetap = new Fluent($snapshot['penetap'] ?? []);

        $calculation = new Fluent($snapshot['calculation'] ?? []);
        $calculation->calculated_at = $this->datetimeOrNull($calculation->calculated_at);
        $calculation->total_alternatif = count($snapshot['results'] ?? []);
        $calculation->total_kriteria = count($snapshot['kriterias'] ?? []);

        return [
            'keputusan' => $keputusan,
            'calculation' => $calculation,
            'results' => collect($snapshot['results'] ?? [])->map(fn (array $result): Fluent => $this->resultObject($result)),
            'selectedDetails' => $keputusan->selected_results,
            'budgetSummary' => $snapshot['budget_summary'] ?? [],
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
        $object->program = new Fluent([
            'id' => $result['usulan_pembangunan_id'] ?? null,
            'nama_kegiatan' => $result['nama_program'] ?? null,
            'lokasi_label' => $result['lokasi'] ?? null,
            'estimasi_anggaran' => $result['estimasi_anggaran'] ?? null,
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
