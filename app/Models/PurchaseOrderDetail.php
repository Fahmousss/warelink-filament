<?php

namespace App\Models;

use App\Policies\PurchaseOrderDetailPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(PurchaseOrderDetailPolicy::class)]
class PurchaseOrderDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'integer',
            'quantity_received' => 'integer',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Attributes
    protected function quantityRemaining(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity_ordered - ($this->quantity_received ?? 0),
        );
    }

    protected function isFullyReceived(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->quantity_received ?? 0) >= $this->quantity_ordered,
        );
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->subtotal = $detail->quantity_ordered * $detail->price;
        });

        static::saved(function ($detail) {
            $detail->purchaseOrder->calculateTotalAmount();
            $detail->purchaseOrder->updateStatus();
        });
    }
}
