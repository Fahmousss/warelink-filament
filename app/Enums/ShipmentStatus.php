<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ShipmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'Draft';
    case SHIPPED = 'Shipped';
    case ARRIVED = 'Arrived';
    case PROCESSED = 'Processed';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SHIPPED => 'Shipped',
            self::ARRIVED => 'Arrived',
            self::PROCESSED => 'Processed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SHIPPED => 'info',
            self::ARRIVED => 'warning',
            self::PROCESSED => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-m-document',
            self::SHIPPED => 'heroicon-m-truck',
            self::ARRIVED => 'heroicon-m-inbox-arrow-down',
            self::PROCESSED => 'heroicon-m-check-badge',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DRAFT => 'Shipment is being prepared',
            self::SHIPPED => 'Shipment is in transit',
            self::ARRIVED => 'Shipment has arrived at warehouse',
            self::PROCESSED => 'Shipment has been processed and received',
        };
    }
}
