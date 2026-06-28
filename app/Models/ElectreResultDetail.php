<?php

namespace App\Models;

use Database\Factories\ElectreResultDetailFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectreResultDetail extends Model
{
    /** @use HasFactory<ElectreResultDetailFactory> */
    use HasFactory;

    public const TAHAP_MATRIKS_KEPUTUSAN = 'matriks_keputusan';

    public const TAHAP_NORMALISASI = 'normalisasi';

    public const TAHAP_PEMBOBOTAN = 'pembobotan';

    public const TAHAP_CONCORDANCE = 'concordance';

    public const TAHAP_DISCORDANCE = 'discordance';

    public const TAHAP_AGGREGATE_DOMINANCE = 'aggregate_dominance';

    public const TAHAPS = [
        self::TAHAP_MATRIKS_KEPUTUSAN,
        self::TAHAP_NORMALISASI,
        self::TAHAP_PEMBOBOTAN,
        self::TAHAP_CONCORDANCE,
        self::TAHAP_DISCORDANCE,
        self::TAHAP_AGGREGATE_DOMINANCE,
    ];

    protected $table = 'electre_result_details';

    protected $fillable = [
        'electre_calculation_id',
        'tahap',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function scopeTahap(Builder $query, string $tahap): Builder
    {
        return $query->where('tahap', $tahap);
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(ElectreCalculation::class, 'electre_calculation_id');
    }
}
