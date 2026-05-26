<?php

namespace App\Models;

use Database\Factories\KriteriaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kriteria extends Model
{
    /** @use HasFactory<KriteriaFactory> */
    use HasFactory, SoftDeletes;

    public const TIPE_BENEFIT = 'benefit';
    public const TIPE_COST = 'cost';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const TIPES = [
        self::TIPE_BENEFIT,
        self::TIPE_COST,
    ];

    public const STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_NONAKTIF,
    ];

    protected $table = 'kriterias';

    protected $fillable = [
        'kode',
        'nama_kriteria',
        'bobot',
        'tipe',
        'deskripsi',
        'urutan',
        'status',
    ];

    protected $appends = [
        'bobot_normal',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'urutan' => 'integer',
        ];
    }

    public function getBobotNormalAttribute(): float
    {
        return (float) $this->bobot / 100;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('kode');
    }

    public function penilaianAlternatifs(): HasMany
    {
        return $this->hasMany(PenilaianAlternatif::class);
    }
}
