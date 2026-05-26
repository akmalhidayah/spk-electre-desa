<?php

namespace App\Models;

use Database\Factories\ElectreResultFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectreResult extends Model
{
    /** @use HasFactory<ElectreResultFactory> */
    use HasFactory;

    protected $table = 'electre_results';

    protected $fillable = [
        'electre_calculation_id',
        'dusun_id',
        'ranking',
        'skor_dominasi',
        'status_prioritas',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'ranking' => 'integer',
            'skor_dominasi' => 'integer',
        ];
    }

    public function scopeRanking(Builder $query): Builder
    {
        return $query->orderBy('ranking')->orderByDesc('skor_dominasi');
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'electre_calculation_id');
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }
}
