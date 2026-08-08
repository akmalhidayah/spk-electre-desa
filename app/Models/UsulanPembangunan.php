<?php

namespace App\Models;

use Database\Factories\UsulanPembangunanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsulanPembangunan extends Model
{
    /** @use HasFactory<UsulanPembangunanFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_DIAJUKAN = 'diajukan';

    public const STATUS_DIPROSES = 'diproses';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    public const TIPE_DUSUN = 'dusun';

    public const TIPE_LINTAS_DUSUN = 'lintas_dusun';

    public const TIPE_UMUM_DESA = 'umum_desa';

    public const TIPE_USULANS = [
        self::TIPE_DUSUN,
        self::TIPE_LINTAS_DUSUN,
        self::TIPE_UMUM_DESA,
    ];

    public const STATUSES = [
        self::STATUS_DIAJUKAN,
        self::STATUS_DIPROSES,
        self::STATUS_DITERIMA,
        self::STATUS_DITOLAK,
    ];

    protected $table = 'usulan_pembangunans';

    protected $fillable = [
        'tahun_perencanaan_id',
        'dusun_id',
        'user_id',
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
        'sumber_dokumen',
        'nomor_dokumen',
        'kategori_kegiatan',
        'jumlah_usulan',
        'estimasi_anggaran',
        'anggaran_realisasi',
        'deskripsi',
        'kondisi_awal',
        'status_usulan',
        'status_pelaksanaan',
        'tahun_realisasi',
        'catatan_admin',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'tahun_realisasi' => 'integer',
            'jumlah_usulan' => 'integer',
            'prakiraan_volume' => 'decimal:2',
            'penerima_manfaat_lk' => 'integer',
            'penerima_manfaat_pr' => 'integer',
            'penerima_manfaat_a_rtm' => 'integer',
            'estimasi_anggaran' => 'decimal:2',
            'anggaran_realisasi' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->whereHas('tahunPerencanaan', fn (Builder $periode) => $periode->where('tahun', $tahun));
    }

    public function scopePeriode(Builder $query, int $periodeId): Builder
    {
        return $query->where('tahun_perencanaan_id', $periodeId);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_usulan', $status);
    }

    public function scopeDiajukan(Builder $query): Builder
    {
        return $query->where('status_usulan', self::STATUS_DIAJUKAN);
    }

    public function scopeDiproses(Builder $query): Builder
    {
        return $query->where('status_usulan', self::STATUS_DIPROSES);
    }

    public function scopeDiterima(Builder $query): Builder
    {
        return $query->where('status_usulan', self::STATUS_DITERIMA);
    }

    public function scopeDitolak(Builder $query): Builder
    {
        return $query->where('status_usulan', self::STATUS_DITOLAK);
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe_usulan', $tipe);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK => 'Ditolak',
        ][$this->status_usulan] ?? ucfirst((string) $this->status_usulan);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_DIAJUKAN => 'badge-warning',
            self::STATUS_DIPROSES => 'badge-info',
            self::STATUS_DITERIMA => 'badge-success',
            self::STATUS_DITOLAK => 'badge-danger',
        ][$this->status_usulan] ?? 'badge-muted';
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

    public function getLokasiLabelAttribute(): string
    {
        if ($this->tipe_usulan === self::TIPE_UMUM_DESA) {
            return 'Desa Barambang';
        }

        if ($this->tipe_usulan === self::TIPE_LINTAS_DUSUN) {
            return $this->dusunsTerkait->pluck('nama_dusun')->join(', ') ?: ($this->lokasi_kegiatan ?: '-');
        }

        return $this->lokasi_kegiatan ?: ($this->dusun?->nama_dusun ?: '-');
    }

    public function getTahunAttribute(): ?int
    {
        return $this->tahunPerencanaan?->tahun;
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function tahunPerencanaan(): BelongsTo
    {
        return $this->belongsTo(TahunPerencanaan::class, 'tahun_perencanaan_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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

    public function penilaianAlternatifs(): HasMany
    {
        return $this->hasMany(PenilaianAlternatif::class);
    }

    public function electreResults(): HasMany
    {
        return $this->hasMany(ElectreResult::class);
    }

    public function keputusanAkhirs(): HasMany
    {
        return $this->hasMany(KeputusanAkhir::class);
    }
}
