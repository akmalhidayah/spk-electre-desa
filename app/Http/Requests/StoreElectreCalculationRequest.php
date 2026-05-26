<?php

namespace App\Http\Requests;

use App\Models\ElectreCalculation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreElectreCalculationRequest extends FormRequest
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
            'kode_perhitungan' => ['required', 'string', 'max:255', 'unique:electre_calculations,kode_perhitungan'],
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000'],
            'judul' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(ElectreCalculation::STATUSES)],
            'total_alternatif' => ['nullable', 'integer', 'min:0'],
            'total_kriteria' => ['nullable', 'integer', 'min:0'],
            'calculated_by' => ['nullable', 'exists:users,id'],
            'calculated_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
