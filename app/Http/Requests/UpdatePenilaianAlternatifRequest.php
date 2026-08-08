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
            'tahun_perencanaan_id' => ['required', 'exists:tahun_perencanaans,id'],
            'usulan_pembangunan_id' => ['required', 'exists:usulan_pembangunans,id'],
            'kriteria_id' => [
                'required',
                'exists:kriterias,id',
                Rule::unique('penilaian_alternatifs', 'kriteria_id')
                    ->where('tahun_perencanaan_id', $this->integer('tahun_perencanaan_id'))
                    ->where('usulan_pembangunan_id', $this->integer('usulan_pembangunan_id'))
                    ->ignore($penilaianId),
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
