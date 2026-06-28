<?php

namespace App\Models;

use Database\Factories\UsulanPembangunanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public const TIPE_DUSUN = 'dusun';

    public const TIPE_LINTAS_DUSUN = 'lintas_dusun';

    public const TIPE_UMUM_DESA = 'umum_desa';

    public const TIPE_USULANS = [
        self::TIPE_DUSUN,
        self::TIPE_LINTAS_DUSUN,
        self::TIPE_UMUM_DESA,
    ];

    public const PRIORITAS_BELUM_DINILAI = 'belum_dinilai';

    public const PRIORITAS_NON_PRIORITAS = 'non_prioritas';

    public const PRIORITAS_PRIORITAS = 'prioritas';

    public const STATUS_PRIORITAS = [
        self::PRIORITAS_BELUM_DINILAI,
        self::PRIORITAS_NON_PRIORITAS,
        self::PRIORITAS_PRIORITAS,
    ];

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
        'tipe_usulan',
        'lokasi_kegiatan',
        'prakiraan_volume',
        'satuan',
        'penerima_manfaat_lk',
        'penerima_manfaat_pr',
        'penerima_manfaat_a_rtm',
        'sdgs_ke',
        'sumber_usulan',
        'kategori_kegiatan',
        'jumlah_usulan',
        'estimasi_anggaran',
        'deskripsi',
        'status',
        'status_prioritas',
        'is_data_pendukung_penilaian',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'jumlah_usulan' => 'integer',
            'prakiraan_volume' => 'decimal:2',
            'penerima_manfaat_lk' => 'integer',
            'penerima_manfaat_pr' => 'integer',
            'penerima_manfaat_a_rtm' => 'integer',
            'estimasi_anggaran' => 'decimal:2',
            'is_data_pendukung_penilaian' => 'boolean',
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

    public function scopeDiajukan(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIAJUKAN);
    }

    public function scopeDiproses(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIPROSES);
    }

    public function scopeDiterima(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DITERIMA);
    }

    public function scopeDiterimaAtauPrioritas(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_DITERIMA,
            self::STATUS_MASUK_PRIORITAS,
        ]);
    }

    public function scopeDitolak(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DITOLAK);
    }

    public function scopeMasukPrioritas(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MASUK_PRIORITAS);
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe_usulan', $tipe);
    }

    public function scopeDataPendukung(Builder $query): Builder
    {
        return $query->where('is_data_pendukung_penilaian', true);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_MASUK_PRIORITAS => 'Masuk Prioritas',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_DIAJUKAN => 'badge-warning',
            self::STATUS_DIPROSES => 'badge-info',
            self::STATUS_DITERIMA => 'badge-success',
            self::STATUS_DITOLAK => 'badge-danger',
            self::STATUS_MASUK_PRIORITAS => 'badge-priority',
        ][$this->status] ?? 'badge-muted';
    }

    public function getTipeUsulanLabelAttribute(): string
    {
        return [
            self::TIPE_DUSUN => 'Usulan Dusun',
            self::TIPE_LINTAS_DUSUN => 'Usulan Lintas Dusun',
            self::TIPE_UMUM_DESA => 'Usulan Umum Desa',
        ][$this->tipe_usulan] ?? 'Usulan';
    }

    public function getTotalPenerimaManfaatAttribute(): int
    {
        return (int) $this->penerima_manfaat_lk + (int) $this->penerima_manfaat_pr;
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dusunsTerkait(): BelongsToMany
    {
        return $this->belongsToMany(Dusun::class, 'dusun_usulan_pembangunan')
            ->withTimestamps();
    }
}
