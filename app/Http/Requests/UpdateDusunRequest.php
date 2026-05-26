<?php

namespace App\Http\Requests;

use App\Models\Dusun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDusunRequest extends FormRequest
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
        $dusun = $this->route('dusun');
        $dusunId = $dusun instanceof Dusun ? $dusun->id : $dusun;

        return [
            'kode_alternatif' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('dusuns', 'kode_alternatif')->ignore($dusunId),
            ],
            'nama_dusun' => [
                'required',
                'string',
                'max:150',
                Rule::unique('dusuns', 'nama_dusun')
                    ->whereNull('deleted_at')
                    ->ignore($dusunId),
            ],
            'luas_tanah' => ['nullable', 'numeric', 'min:0'],
            'jumlah_penduduk' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', Rule::in(Dusun::STATUSES)],
        ];
    }
}
