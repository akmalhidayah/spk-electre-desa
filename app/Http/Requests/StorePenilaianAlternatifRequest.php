<?php

namespace App\Http\Requests;

use App\Models\PenilaianAlternatif;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenilaianAlternatifRequest extends FormRequest
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
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000'],
            'dusun_id' => ['required', 'exists:dusuns,id'],
            'kriteria_id' => [
                'required',
                'exists:kriterias,id',
                Rule::unique('penilaian_alternatifs', 'kriteria_id')
                    ->where('tahun', $this->integer('tahun'))
                    ->where('dusun_id', $this->integer('dusun_id')),
            ],
            'nilai' => [
                'required',
                'integer',
                'min:'.PenilaianAlternatif::NILAI_MIN,
                'max:'.PenilaianAlternatif::NILAI_MAX,
            ],
            'keterangan' => ['nullable', 'string'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
