<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'quantity_received',
        'quantity_accepted',
        'quantity_rejected',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'integer',
            'quantity_accepted' => 'integer',
            'quantity_rejected' => 'integer',
        ];
    }

    // Relationships
    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Attributes
    protected function hasRejections(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->quantity_rejected ?? 0) > 0,
        );
    }

    protected function acceptanceRate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->quantity_received <= 0) {
                    return 0;
                }

                return round(($this->quantity_accepted / $this->quantity_received) * 100, 2);
            },
        );
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            // Auto-calculate accepted if not set
            if (is_null($detail->quantity_accepted)) {
                $detail->quantity_accepted = $detail->quantity_received - ($detail->quantity_rejected ?? 0);
            }

            // Validate quantities
            if ($detail->quantity_accepted + $detail->quantity_rejected > $detail->quantity_received) {
                throw new \Exception('Accepted + Rejected quantities cannot exceed Received quantity');
            }
        });
    }
}
