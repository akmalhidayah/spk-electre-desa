<?php

namespace App\Models;

use Database\Factories\UsulanPembangunanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsulanPembangunan extends Model
{
    /** @use HasFactory<UsulanPembangunanFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_MASUK_PRIORITAS = 'masuk_prioritas';

    public const STATUSES = [
        self::STATUS_DIAJUKAN,
        self::STATUS_DIPROSES,
        self::STATUS_DITERIMA,
        self::STATUS_DITOLAK,
        self::STATUS_MASUK_PRIORITAS,
    ];

    protected $table = 'usulan_pembangunans';

    protected $fillable = [
        'dusun_id',
        'user_id',
        'tahun',
        'nama_kegiatan',
        'jumlah_usulan',
        'estimasi_anggaran',
        'deskripsi',
        'status',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'jumlah_usulan' => 'integer',
            'estimasi_anggaran' => 'decimal:2',
        ];
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
