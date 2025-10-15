<?php

// declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'product_id',
        'quantity_shipped',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_shipped' => 'integer',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // BOOT METHOD
    // ==========================================

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (ShipmentDetail $detail): void {
            // Validate quantity is positive
            if ($detail->quantity_shipped <= 0) {
                throw new \InvalidArgumentException('Quantity shipped must be greater than zero');
            }
        });
    }
}
