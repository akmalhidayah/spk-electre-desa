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
            'tahun_perencanaan_id' => ['required', 'exists:tahun_perencanaans,id'],
            'usulan_pembangunan_id' => ['required', 'exists:usulan_pembangunans,id'],
            'kriteria_id' => [
                'required',
                'exists:kriterias,id',
                Rule::unique('penilaian_alternatifs', 'kriteria_id')
                    ->where('tahun_perencanaan_id', $this->integer('tahun_perencanaan_id'))
                    ->where('usulan_pembangunan_id', $this->integer('usulan_pembangunan_id')),
            ],
            'nilai' => [
                'required',
                'integer',
                'min:'.PenilaianAlternatif::NILAI_MIN,
                'max:'.PenilaianAlternatif::NILAI_MAX,
            ],
            'keterangan' => ['nullable', 'string'],
            'sumber_data' => ['nullable', 'string', 'max:255'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
