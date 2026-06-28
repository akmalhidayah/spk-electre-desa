<?php

namespace App\Models;

use Database\Factories\PenilaianAlternatifFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianAlternatif extends Model
{
    /** @use HasFactory<PenilaianAlternatifFactory> */
    use HasFactory;

    public const NILAI_MIN = 1;

    public const NILAI_MAX = 5;

    protected $table = 'penilaian_alternatifs';

    protected $fillable = [
        'tahun',
        'dusun_id',
        'kriteria_id',
        'nilai',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'nilai' => 'integer',
        ];
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByDusun(Builder $query, int $dusunId): Builder
    {
        return $query->where('dusun_id', $dusunId);
    }

    public function scopeByKriteria(Builder $query, int $kriteriaId): Builder
    {
        return $query->where('kriteria_id', $kriteriaId);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
