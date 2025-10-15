<?php

namespace App\Models;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

#[UsePolicy(\App\Policies\ProductPolicy::class)]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'description',
        'unit',
        'price',
        'stock_quantity',
        'minimum_stock',
        'is_active',
        'supplier_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all purchase order details for this product
     */
    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    /**
     * Get all goods receipt details for this product
     */
    public function goodsReceiptDetails(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class);
    }

    /**
     * Get all shipment details for this product
     */
    public function shipmentDetails(): HasMany
    {
        return $this->hasMany(ShipmentDetail::class);
    }

    /**
     * Get the supplier that provides this product
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope a query to only include active products
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include low stock products
     */
    #[Scope]
    protected function lowStock(Builder $query): void
    {
        $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include out of stock products
     */
    #[Scope]
    protected function outOfStock(Builder $query): void
    {
        $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope a query to only include products with good stock levels
     */
    #[Scope]
    protected function goodStock(Builder $query): void
    {
        $query->whereColumn('stock_quantity', '>', 'minimum_stock');
    }

    /**
     * Scope a query to products that need reordering
     */
    #[Scope]
    protected function needsReorder(Builder $query): void
    {
        $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('is_active', true);
    }

    /**
     * Scope a query to products by a specific supplier
     */
    #[Scope]
    protected function bySupplier(Builder $query, int $supplierId): void
    {
        $query->where('supplier_id', $supplierId);
    }

    // ==========================================
    // ATTRIBUTES & ACCESSORS
    // ==========================================

    /**
     * Check if the product is low on stock
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity <= $this->minimum_stock && $this->stock_quantity > 0,
        );
    }

    /**
     * Check if the product is out of stock
     */
    protected function isOutOfStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity <= 0,
        );
    }

    /**
     * Check if the product has good stock levels
     */
    protected function hasGoodStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity > ($this->minimum_stock * 2),
        );
    }

    /**
     * Get the stock status as a string
     */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => match (true) {
                $this->stock_quantity <= 0 => 'out_of_stock',
                $this->stock_quantity <= $this->minimum_stock => 'low_stock',
                $this->stock_quantity <= ($this->minimum_stock * 2) => 'moderate',
                default => 'good',
            },
        );
    }

    /**
     * Get the stock status label
     */
    protected function stockStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->stock_status) {
                'out_of_stock' => 'Out of Stock',
                'low_stock' => 'Low Stock',
                'moderate' => 'Moderate Stock',
                'good' => 'Good Stock',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get the stock status color
     */
    protected function stockStatusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->stock_status) {
                'out_of_stock' => 'danger',
                'low_stock' => 'warning',
                'moderate' => 'info',
                'good' => 'success',
                default => 'gray',
            },
        );
    }

    /**
     * Calculate stock percentage relative to minimum
     */
    protected function stockPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->minimum_stock <= 0) {
                    return 100;
                }

                return round(($this->stock_quantity / $this->minimum_stock) * 100, 2);
            },
        );
    }

    /**
     * Calculate remaining stock until minimum
     */
    protected function stockUntilMinimum(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->minimum_stock - $this->stock_quantity),
        );
    }

    /**
     * Get formatted price with currency
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->price, 0, ',', '.'),
        );
    }

    /**
     * Get stock value (quantity × price)
     */
    protected function stockValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity * $this->price,
        );
    }

    /**
     * Get formatted stock value
     */
    protected function formattedStockValue(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->stock_value, 0, ',', '.'),
        );
    }

    // ==========================================
    // STOCK MANAGEMENT METHODS
    // ==========================================

    /**
     * Increase product stock quantity
     */
    public function increaseStock(int $quantity, ?string $reason = null): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $this->increment('stock_quantity', $quantity);

        // Optional: Log stock movement
        $this->logStockMovement('increase', $quantity, $reason);
    }

    /**
     * Decrease product stock quantity
     */
    public function decreaseStock(int $quantity, ?string $reason = null): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        if ($this->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$this->stock_quantity}, Required: {$quantity}");
        }

        $this->decrement('stock_quantity', $quantity);

        // Optional: Log stock movement
        $this->logStockMovement('decrease', $quantity, $reason);
    }

    /**
     * Set stock quantity directly
     */
    public function setStock(int $quantity, ?string $reason = null): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        $oldQuantity = $this->stock_quantity;
        $this->stock_quantity = $quantity;
        $this->save();

        // Optional: Log stock adjustment
        $this->logStockMovement('adjustment', $quantity - $oldQuantity, $reason);
    }

    /**
     * Check if sufficient stock is available
     */
    public function hasSufficientStock(int $requiredQuantity): bool
    {
        return $this->stock_quantity >= $requiredQuantity;
    }

    /**
     * Reserve stock (for orders, etc.)
     */
    public function reserveStock(int $quantity): void
    {
        if (! $this->hasSufficientStock($quantity)) {
            throw new \Exception("Cannot reserve stock. Available: {$this->stock_quantity}, Required: {$quantity}");
        }

        // Implement reservation logic if needed
        // For now, just decrease stock
        $this->decreaseStock($quantity, 'Reserved for order');
    }

    // ==========================================
    // REORDER MANAGEMENT
    // ==========================================

    /**
     * Check if product needs reordering
     */
    public function isNeedsReorder(): bool
    {
        return $this->is_active &&
               $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Calculate suggested reorder quantity
     */
    public function suggestedReorderQuantity(): int
    {
        // Reorder enough to reach 2x minimum stock
        $targetStock = $this->minimum_stock * 2;

        return max(0, $targetStock - $this->stock_quantity);
    }

    /**
     * Get reorder urgency level
     */
    public function reorderUrgency(): string
    {
        return match (true) {
            $this->stock_quantity <= 0 => 'critical',
            $this->stock_quantity <= ($this->minimum_stock * 0.5) => 'high',
            $this->stock_quantity <= $this->minimum_stock => 'medium',
            default => 'low',
        };
    }

    // ==========================================
    // STATISTICS & ANALYTICS
    // ==========================================

    /**
     * Get total quantity ordered from all POs
     */
    public function getTotalOrdered(): int
    {
        return $this->purchaseOrderDetails()->sum('quantity_ordered');
    }

    /**
     * Get total quantity received from all GRs
     */
    public function getTotalReceived(): int
    {
        return $this->goodsReceiptDetails()
            ->whereHas('goodsReceipt', fn ($q) => $q->where('status', GoodsReceiptStatus::COMPLETED))
            ->sum('quantity_accepted');
    }

    /**
     * Get total quantity rejected from all GRs
     */
    public function getTotalRejected(): int
    {
        return $this->goodsReceiptDetails()
            ->whereHas('goodsReceipt', fn ($q) => $q->where('status', GoodsReceiptStatus::COMPLETED))
            ->sum('quantity_rejected');
    }

    /**
     * Calculate overall acceptance rate
     */
    public function getAcceptanceRate(): float
    {
        $totalReceived = $this->getTotalReceived();
        $totalRejected = $this->getTotalRejected();
        $total = $totalReceived + $totalRejected;

        if ($total <= 0) {
            return 100;
        }

        return round(($totalReceived / $total) * 100, 2);
    }

    /**
     * Get pending PO quantity (ordered but not yet received)
     */
    public function getPendingQuantity(): int
    {
        return $this->purchaseOrderDetails()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', [PurchaseOrderStatus::PENDING, PurchaseOrderStatus::PARTIAL])
            )
            ->get()
            ->sum(function ($detail) {
                return $detail->quantity_ordered - $detail->quantity_received;
            });
    }

    /**
     * Get projected stock (current + pending)
     */
    public function getProjectedStock(): int
    {
        return $this->stock_quantity + $this->getPendingQuantity();
    }

    // ==========================================
    // VALIDATION METHODS
    // ==========================================

    /**
     * Validate if product is available for sale/use
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }

    /**
     * Validate if product can be ordered
     */
    public function canBeOrdered(): bool
    {
        return $this->is_active;
    }

    // ==========================================
    // UTILITY METHODS
    // ==========================================

    /**
     * Log stock movement (optional - implement if stock audit is needed)
     */
    protected function logStockMovement(string $type, int $quantity, ?string $reason): void
    {
        // Implement stock movement logging if needed
        // Example: StockMovement::create([...])

        Log::info("Stock movement for Product #{$this->id}", [
            'product_code' => $this->product_code,
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'stock_before' => $this->stock_quantity - ($type === 'increase' ? $quantity : -$quantity),
            'stock_after' => $this->stock_quantity,
        ]);
    }

    /**
     * Get a summary of the product
     */
    public function getSummary(): array
    {
        return [
            'code' => $this->product_code,
            'name' => $this->name,
            'price' => $this->formatted_price,
            'stock' => $this->stock_quantity,
            'minimum' => $this->minimum_stock,
            'status' => $this->stock_status_label,
            'value' => $this->formatted_stock_value,
            'active' => $this->is_active,
        ];
    }

    /**
     * Get the display name with code
     */
    public function getFullName(): string
    {
        return "{$this->product_code} - {$this->name}";
    }

    // ==========================================
    // BOOT METHOD
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate product code if not provided
        static::creating(function ($product) {
            if (empty($product->product_code)) {
                $product->product_code = self::generateProductCode($product->supplier_id);
            }
        });

        // Log when stock becomes low
        static::updated(function ($product) {
            if ($product->isDirty('stock_quantity')) {
                if ($product->is_low_stock && ! $product->getOriginal('stock_quantity') <= $product->minimum_stock) {
                    // Stock just became low - trigger notification/alert
                    // Notification::send(...) or event(new LowStockAlert($product));
                    Log::warning("Low stock alert for Product #{$product->id}: {$product->product_code}");
                }

                if ($product->is_out_of_stock && $product->getOriginal('stock_quantity') > 0) {
                    // Stock just ran out - trigger critical alert
                    // Notification::send(...) or event(new OutOfStockAlert($product));
                    Log::critical("Out of stock alert for Product #{$product->id}: {$product->product_code}");
                }
            }
        });
    }

    /**
     * Generate unique product code
     */
    protected static function generateProductCode(string $supplier_code): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now())->count() + 1;
        $supplier = Supplier::find($supplier_code)->code;

        return 'PROD-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT).'-'.$supplier;
    }
}
