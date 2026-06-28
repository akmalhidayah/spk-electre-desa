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
            'tanda_tangan' => ['nullable', 'string', 'starts_with:data:image/png;base64,', 'max:500000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $signature = $this->input('tanda_tangan');

            if (! is_string($signature) || $signature === '') {
                return;
            }

            $prefix = 'data:image/png;base64,';

            if (! str_starts_with($signature, $prefix)) {
                return;
            }

            $binary = base64_decode(substr($signature, strlen($prefix)), true);

            if ($binary === false) {
                $validator->errors()->add('tanda_tangan', 'Tanda tangan digital tidak valid.');

                return;
            }

            if (strlen($binary) > 350000) {
                $validator->errors()->add('tanda_tangan', 'Ukuran tanda tangan digital terlalu besar.');

                return;
            }

            $imageInfo = @getimagesizefromstring($binary);

            if (! $imageInfo || ($imageInfo['mime'] ?? null) !== 'image/png') {
                $validator->errors()->add('tanda_tangan', 'Tanda tangan digital harus berupa gambar PNG.');
            }
        });
    }
}
