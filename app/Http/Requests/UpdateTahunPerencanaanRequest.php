<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTahunPerencanaanRequest extends FormRequest
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
            'tahun' => [
                'required',
                'integer',
                'min:2020',
                'max:2100',
                Rule::unique('tahun_perencanaans', 'tahun')->ignore($this->route('tahunPerencanaan')),
            ],
            'nama_periode' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_locked' => ['nullable', 'boolean'],
        ];
    }
}
