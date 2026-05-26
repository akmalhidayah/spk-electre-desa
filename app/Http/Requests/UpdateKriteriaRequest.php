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
                'max:255',
                Rule::unique('kriterias', 'kode')->ignore($kriteriaId),
            ],
            'nama_kriteria' => ['required', 'string', 'max:255'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'tipe' => ['required', Rule::in(Kriteria::TIPES)],
            'deskripsi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Kriteria::STATUSES)],
        ];
    }
}
