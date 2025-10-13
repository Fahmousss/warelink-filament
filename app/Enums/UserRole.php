<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasIcon, HasLabel
{
    case Supplier = 'Supplier';
    case Checker = 'Checker';
    case Admin = 'Admin';
    case Accounting = 'Accounting';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Supplier => 'heroicon-m-truck',
            self::Checker => 'heroicon-m-check-badge',
            self::Admin => 'heroicon-m-shield-check',
            self::Accounting => 'heroicon-m-currency-dollar',
        };
    }
}
