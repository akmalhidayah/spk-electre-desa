<?php

namespace App\Http\Requests;

use App\Models\UsulanPembangunan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsulanPembangunanRequest extends FormRequest
{
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
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nama_kegiatan' => ['required', 'string', 'max:200'],
            'jumlah_usulan' => ['nullable', 'integer', 'min:0'],
            'estimasi_anggaran' => ['nullable', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(UsulanPembangunan::STATUSES)],
            'catatan_admin' => ['nullable', 'string'],
        ];
    }
}
