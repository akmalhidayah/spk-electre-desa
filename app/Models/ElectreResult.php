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
        'usulan_pembangunan_id',
        'kode_alternatif',
        'nama_program_snapshot',
        'lokasi_snapshot',
        'nama_dusun_snapshot',
        'ranking',
        'skor_dominasi',
        'total_preferensi',
        'status_prioritas',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'ranking' => 'integer',
            'skor_dominasi' => 'integer',
            'total_preferensi' => 'decimal:8',
        ];
    }

    public function scopeRanking(Builder $query): Builder
    {
        return $query->orderBy('ranking')->orderByDesc('skor_dominasi')->orderByDesc('total_preferensi');
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'electre_calculation_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(UsulanPembangunan::class, 'usulan_pembangunan_id');
    }

    public function usulanPembangunan(): BelongsTo
    {
        return $this->program();
    }
}
