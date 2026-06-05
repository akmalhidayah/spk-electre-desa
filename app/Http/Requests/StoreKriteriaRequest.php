<?php

namespace App\Http\Requests;

use App\Models\Kriteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKriteriaRequest extends FormRequest
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
            'kode' => ['required', 'string', 'max:20', 'unique:kriterias,kode'],
            'nama_kriteria' => ['required', 'string', 'max:150', 'unique:kriterias,nama_kriteria'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'tipe' => ['required', Rule::in(Kriteria::TIPES)],
            'deskripsi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Kriteria::STATUSES)],
        ];
    }
}
