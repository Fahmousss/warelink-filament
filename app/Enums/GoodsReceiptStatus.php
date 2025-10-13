<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum GoodsReceiptStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'Pending';
    case VERIFIED = 'Verified';
    case COMPLETED = 'Completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::COMPLETED => 'Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'info',
            self::COMPLETED => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-m-clock',
            self::VERIFIED => 'heroicon-m-clipboard-document-check',
            self::COMPLETED => 'heroicon-m-check-badge',
        };
    }
}
