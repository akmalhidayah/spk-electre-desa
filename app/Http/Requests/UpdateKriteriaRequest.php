<?php

namespace App\Http\Requests;

use App\Models\Kriteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKriteriaRequest extends FormRequest
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
        $kriteria = $this->route('kriteria');
        $kriteriaId = $kriteria instanceof Kriteria ? $kriteria->id : $kriteria;

        return [
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kriterias', 'kode')->ignore($kriteriaId),
            ],
            'nama_kriteria' => [
                'required',
                'string',
                'max:150',
                Rule::unique('kriterias', 'nama_kriteria')->ignore($kriteriaId),
            ],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'tipe' => ['required', Rule::in(Kriteria::TIPES)],
            'deskripsi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Kriteria::STATUSES)],
        ];
    }
}
