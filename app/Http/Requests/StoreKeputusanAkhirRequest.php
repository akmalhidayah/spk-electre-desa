<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKeputusanAkhirRequest extends FormRequest
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
            'electre_calculation_id' => ['required', 'exists:electre_calculations,id'],
            'dusun_id' => ['required', 'exists:dusuns,id'],
            'nomor_keputusan' => ['nullable', 'string', 'max:100'],
            'tanggal_keputusan' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'ditetapkan'])],
            'dasar_pertimbangan' => ['nullable', 'string'],
            'catatan_keputusan' => ['nullable', 'string'],
        ];
    }
}
