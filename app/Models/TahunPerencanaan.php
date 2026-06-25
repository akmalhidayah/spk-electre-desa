<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahunPerencanaan extends Model
{
    protected $table = 'tahun_perencanaans';

    protected $fillable = [
        'tahun',
        'nama_periode',
        'deskripsi',
        'is_active',
        'is_locked',
        'perlu_hitung_ulang',
        'alasan_hitung_ulang',
        'last_data_changed_at',
        'last_electre_calculation_id',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'perlu_hitung_ulang' => 'boolean',
            'last_data_changed_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function lastElectreCalculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'last_electre_calculation_id');
    }
}
