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
        'tahun_perencanaan_id',
        'usulan_pembangunan_id',
        'kriteria_id',
        'nilai',
        'keterangan',
        'sumber_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
        ];
    }

    public function scopePeriode(Builder $query, int $periodeId): Builder
    {
        return $query->where('tahun_perencanaan_id', $periodeId);
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->whereHas('tahunPerencanaan', fn (Builder $periode) => $periode->where('tahun', $tahun));
    }

    public function scopeProgram(Builder $query, int $programId): Builder
    {
        return $query->where('usulan_pembangunan_id', $programId);
    }

    public function scopeByKriteria(Builder $query, int $kriteriaId): Builder
    {
        return $query->where('kriteria_id', $kriteriaId);
    }

    public function tahunPerencanaan(): BelongsTo
    {
        return $this->belongsTo(TahunPerencanaan::class);
    }

    public function usulanPembangunan(): BelongsTo
    {
        return $this->belongsTo(UsulanPembangunan::class);
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
