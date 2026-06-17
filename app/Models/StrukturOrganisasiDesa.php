<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StrukturOrganisasiDesa extends Model
{
    protected $fillable = [
        'welcome_desa_setting_id',
        'nama',
        'jabatan',
        'foto',
        'deskripsi',
        'urutan',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'status_aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function welcomeDesaSetting(): BelongsTo
    {
        return $this->belongsTo(WelcomeDesaSetting::class);
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }
}
