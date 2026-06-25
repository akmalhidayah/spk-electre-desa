<?php

namespace App\Http\Requests;

use App\Models\UsulanPembangunan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsulanPembangunanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tipe_usulan' => $this->input('tipe_usulan', UsulanPembangunan::TIPE_DUSUN),
            'dusun_id' => $this->input('dusun_id', $this->user()?->dusun_id),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipe_usulan' => ['required', Rule::in(UsulanPembangunan::TIPE_USULANS)],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'dusun_terkait_ids' => ['nullable', 'array', 'min:2'],
            'dusun_terkait_ids.*' => ['integer', 'exists:dusuns,id'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'prakiraan_volume' => ['nullable', 'numeric', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'penerima_manfaat_lk' => ['nullable', 'integer', 'min:0'],
            'penerima_manfaat_pr' => ['nullable', 'integer', 'min:0'],
            'penerima_manfaat_a_rtm' => ['nullable', 'integer', 'min:0'],
            'kategori_kegiatan' => ['nullable', 'string', 'max:100'],
            'sdgs_ke' => ['nullable', 'string', 'max:50'],
            'sumber_usulan' => ['nullable', 'string', 'max:255'],
            'jumlah_usulan' => ['nullable', 'integer', 'min:0'],
            'estimasi_anggaran' => ['nullable', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(UsulanPembangunan::STATUSES)],
            'status_prioritas' => ['nullable', Rule::in(UsulanPembangunan::STATUS_PRIORITAS)],
            'is_data_pendukung_penilaian' => ['nullable', 'boolean'],
            'catatan_admin' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('tipe_usulan') === UsulanPembangunan::TIPE_DUSUN && ! $this->filled('dusun_id')) {
                $validator->errors()->add('dusun_id', 'Dusun wajib dipilih untuk usulan dusun.');
            }

            if ($this->input('tipe_usulan') === UsulanPembangunan::TIPE_LINTAS_DUSUN && count($this->input('dusun_terkait_ids', [])) < 2) {
                $validator->errors()->add('dusun_terkait_ids', 'Pilih minimal dua dusun terkait untuk usulan lintas dusun.');
            }
        });
    }
}
