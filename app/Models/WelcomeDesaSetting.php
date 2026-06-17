<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class WelcomeDesaSetting extends Model
{
    protected $fillable = [
        'nama_desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'alamat',
        'email',
        'telepon',
        'logo_desa',
        'hero_image',
        'judul_welcome',
        'deskripsi_welcome',
        'visi',
        'misi',
        'judul_infografis',
        'deskripsi_infografis',
        'maps_embed',
        'maps_link',
        'gambar_peta',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function strukturOrganisasi(): HasMany
    {
        return $this->hasMany(StrukturOrganisasiDesa::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_desa ? Storage::disk('public')->url($this->logo_desa) : null;
    }

    public function heroImageUrl(): ?string
    {
        return $this->hero_image ? Storage::disk('public')->url($this->hero_image) : null;
    }

    public function gambarPetaUrl(): ?string
    {
        return $this->gambar_peta ? Storage::disk('public')->url($this->gambar_peta) : null;
    }
}
