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

    public const TYPE_REGULAR = 'REGULER';

    public const TYPE_TESTING = 'PENGUJIAN';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    protected $table = 'electre_calculations';

    protected $fillable = [
        'tahun_perencanaan_id',
        'kode_perhitungan',
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
            'versi' => 'integer',
            'is_latest' => 'boolean',
            'total_alternatif' => 'integer',
            'total_kriteria' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    public function getTahunAttribute(): ?int
    {
        return $this->tahunPerencanaan?->tahun;
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->whereHas('tahunPerencanaan', fn (Builder $periode) => $periode->where('tahun', $tahun));
    }

    public function scopePeriode(Builder $query, int $periodeId): Builder
    {
        return $query->where('tahun_perencanaan_id', $periodeId);
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->where('is_latest', true);
    }

    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('notes', 'like', 'JENIS_PERHITUNGAN='.self::TYPE_REGULAR.'%');
    }

    public function scopeTesting(Builder $query): Builder
    {
        return $query->where('notes', 'like', 'JENIS_PERHITUNGAN='.self::TYPE_TESTING.'%');
    }

    public function isRegular(): bool
    {
        return str_contains((string) $this->notes, 'JENIS_PERHITUNGAN='.self::TYPE_REGULAR);
    }

    public function isTesting(): bool
    {
        return str_contains((string) $this->notes, 'JENIS_PERHITUNGAN='.self::TYPE_TESTING);
    }

    public function isOfficialResult(): bool
    {
        return $this->status === self::STATUS_SELESAI && $this->isRegular();
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function tahunPerencanaan(): BelongsTo
    {
        return $this->belongsTo(TahunPerencanaan::class, 'tahun_perencanaan_id');
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
        return $this->hasOne(KeputusanAkhir::class, 'electre_calculation_id')
            ->whereIn('status', ['draft', 'ditetapkan']);
    }
}
