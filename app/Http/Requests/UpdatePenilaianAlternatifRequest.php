<?php

namespace App\Http\Requests;

use App\Models\PenilaianAlternatif;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenilaianAlternatifRequest extends FormRequest
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
        $penilaian = $this->route('penilaian_alternatif')
            ?? $this->route('penilaianAlternatif');
        $penilaianId = $penilaian instanceof PenilaianAlternatif
            ? $penilaian->id
            : $penilaian;

        return [
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000'],
            'dusun_id' => ['required', 'exists:dusuns,id'],
            'kriteria_id' => [
                'required',
                'exists:kriterias,id',
                Rule::unique('penilaian_alternatifs', 'kriteria_id')
                    ->where('tahun', $this->integer('tahun'))
                    ->where('dusun_id', $this->integer('dusun_id'))
                    ->ignore($penilaianId),
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
