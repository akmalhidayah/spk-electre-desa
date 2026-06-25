<?php

namespace App\Models;

use Database\Factories\ElectreCalculationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ElectreCalculation extends Model
{
    /** @use HasFactory<ElectreCalculationFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    protected $table = 'electre_calculations';

    protected $fillable = [
        'kode_perhitungan',
        'tahun',
        'judul',
        'deskripsi',
        'status',
        'versi',
        'is_latest',
        'total_alternatif',
        'total_kriteria',
        'calculated_by',
        'calculated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'versi' => 'integer',
            'is_latest' => 'boolean',
            'total_alternatif' => 'integer',
            'total_kriteria' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->where('is_latest', true);
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ElectreResult::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ElectreResultDetail::class);
    }

    public function keputusanAkhir(): HasOne
    {
        return $this->hasOne(\App\Models\KeputusanAkhir::class, 'electre_calculation_id')
            ->whereIn('status', ['draft', 'ditetapkan']);
    }
}
