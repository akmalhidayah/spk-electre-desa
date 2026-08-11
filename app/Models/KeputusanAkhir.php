<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeputusanAkhir extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DITETAPKAN = 'ditetapkan';

    protected $table = 'keputusan_akhirs';

    protected $fillable = [
        'electre_calculation_id',
        'electre_result_id',
        'usulan_pembangunan_id',
        'tahun_perencanaan_id',
        'nomor_keputusan',
        'tanggal_keputusan',
        'status',
        'dasar_pertimbangan',
        'catatan_keputusan',
        'tanda_tangan',
        'snapshot_data',
        'pdf_path',
        'pdf_hash',
        'snapshotted_at',
        'ditetapkan_oleh',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keputusan' => 'date',
            'decided_at' => 'datetime',
            'snapshot_data' => 'array',
            'snapshotted_at' => 'datetime',
        ];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'electre_calculation_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(UsulanPembangunan::class, 'usulan_pembangunan_id');
    }

    public function tahunPerencanaan(): BelongsTo
    {
        return $this->belongsTo(TahunPerencanaan::class, 'tahun_perencanaan_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(ElectreResult::class, 'electre_result_id');
    }

    public function penetap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(KeputusanAkhirDetail::class)->orderBy('ranking_snapshot');
    }
}
