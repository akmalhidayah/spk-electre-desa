<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KEPALA_DUSUN = 'kepala_dusun';

    public const ROLE_KEPALA_DESA = 'kepala_desa';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_KEPALA_DUSUN,
        self::ROLE_KEPALA_DESA,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'dusun_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function usulanPembangunans(): HasMany
    {
        return $this->hasMany(UsulanPembangunan::class);
    }

    public function penilaianAlternatifs(): HasMany
    {
        return $this->hasMany(PenilaianAlternatif::class, 'created_by');
    }

    public function electreCalculations(): HasMany
    {
        return $this->hasMany(ElectreCalculation::class, 'calculated_by');
    }

    public function keputusanAkhirs(): HasMany
    {
        return $this->hasMany(KeputusanAkhir::class, 'ditetapkan_oleh');
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin / Perangkat Desa',
            self::ROLE_KEPALA_DESA => 'Kepala Desa',
            self::ROLE_KEPALA_DUSUN => 'Kepala Dusun',
            default => 'Pengguna',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKepalaDusun(): bool
    {
        return $this->role === self::ROLE_KEPALA_DUSUN;
    }

    public function isKepalaDesa(): bool
    {
        return $this->role === self::ROLE_KEPALA_DESA;
    }
}
