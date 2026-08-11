<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeputusanAkhirDetail extends Model
{
    protected $fillable = [
        'keputusan_akhir_id', 'electre_result_id', 'usulan_pembangunan_id',
        'kode_alternatif_snapshot', 'nama_program_snapshot', 'lokasi_snapshot',
        'nama_dusun_snapshot', 'ranking_snapshot', 'skor_dominasi_snapshot',
        'estimasi_anggaran_snapshot',
    ];

    protected function casts(): array
    {
        return ['ranking_snapshot' => 'integer', 'skor_dominasi_snapshot' => 'integer', 'estimasi_anggaran_snapshot' => 'decimal:2'];
    }

    public function keputusan(): BelongsTo
    {
        return $this->belongsTo(KeputusanAkhir::class, 'keputusan_akhir_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(ElectreResult::class, 'electre_result_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(UsulanPembangunan::class, 'usulan_pembangunan_id');
    }
}
