<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[UsePolicy(\App\Policies\UserPolicy::class)]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'supplier_id',
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
            'role' => \App\Enums\UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::Admin;
    }

    public function isAccounting(): bool
    {
        return $this->role === \App\Enums\UserRole::Accounting;
    }

    public function isChecker(): bool
    {
        return $this->role === \App\Enums\UserRole::Checker;
    }

    public function isSupplier(): bool
    {
        return $this->role === \App\Enums\UserRole::Supplier;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->supplier_id === $tenant->id;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->supplier ? collect([$this->supplier]) : collect([]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'supplier') {
            return $this->isSupplier() && $this->isActive();
        }

        return $this->isActive();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return Filament::getUserAvatarUrl($this);
    }
}
