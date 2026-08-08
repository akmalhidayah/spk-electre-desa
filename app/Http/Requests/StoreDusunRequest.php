<?php

namespace App\Http\Requests;

use App\Models\Dusun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDusunRequest extends FormRequest
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
            'nama_dusun' => [
                'required',
                'string',
                'max:150',
                Rule::unique('dusuns', 'nama_dusun')->whereNull('deleted_at'),
            ],
            'luas_wilayah' => ['nullable', 'numeric', 'min:0'],
            'jumlah_penduduk' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', Rule::in(Dusun::STATUSES)],
        ];
    }
}
