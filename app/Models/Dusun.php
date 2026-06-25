<?php

namespace App\Models;

use Database\Factories\DusunFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dusun extends Model
{
    /** @use HasFactory<DusunFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_NONAKTIF,
    ];

    protected $table = 'dusuns';

    protected $fillable = [
        'kode_alternatif',
        'nama_dusun',
        'luas_tanah',
        'jumlah_penduduk',
        'keterangan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'luas_tanah' => 'decimal:2',
            'jumlah_penduduk' => 'integer',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeNonaktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NONAKTIF);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function usulanPembangunans(): HasMany
    {
        return $this->hasMany(UsulanPembangunan::class);
    }

    public function usulanPembangunanTerkait(): BelongsToMany
    {
        return $this->belongsToMany(UsulanPembangunan::class, 'dusun_usulan_pembangunan')
            ->withTimestamps();
    }

    public function penilaianAlternatifs(): HasMany
    {
        return $this->hasMany(PenilaianAlternatif::class);
    }

    public function electreResults(): HasMany
    {
        return $this->hasMany(ElectreResult::class);
    }
}
