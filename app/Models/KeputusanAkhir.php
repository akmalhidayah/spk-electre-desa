<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeputusanAkhir extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DITETAPKAN = 'ditetapkan';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $table = 'keputusan_akhirs';

    protected $fillable = [
        'electre_calculation_id',
        'electre_result_id',
        'dusun_id',
        'tahun',
        'nomor_keputusan',
        'tanggal_keputusan',
        'status',
        'dasar_pertimbangan',
        'catatan_keputusan',
        'tanda_tangan',
        'catatan',
        'ditetapkan_oleh',
        'decided_by',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keputusan' => 'date',
            'decided_at' => 'datetime',
        ];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'electre_calculation_id');
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(ElectreResult::class, 'electre_result_id');
    }

    public function penetap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
