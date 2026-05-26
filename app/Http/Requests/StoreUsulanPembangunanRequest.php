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
            'dusun_id' => ['required', 'exists:dusuns,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'jumlah_usulan' => ['nullable', 'integer', 'min:0'],
            'estimasi_anggaran' => ['nullable', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(UsulanPembangunan::STATUSES)],
            'catatan_admin' => ['nullable', 'string'],
        ];
    }
}
