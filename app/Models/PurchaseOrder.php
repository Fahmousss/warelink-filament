<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Policies\PurchaseOrderPolicy;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(PurchaseOrderPolicy::class)]
class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'total_amount' => 'decimal:2',
            'status' => PurchaseOrderStatus::class,
        ];
    }

    // Relationships

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    // Scopes
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', PurchaseOrderStatus::PENDING);
    }

    #[Scope]
    protected function partial(Builder $query): void
    {
        $query->where('status', PurchaseOrderStatus::PARTIAL);
    }

    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->where('status', PurchaseOrderStatus::COMPLETED);
    }

    #[Scope]
    protected function cancelled(Builder $query): void
    {
        $query->where('status', PurchaseOrderStatus::CANCELLED);
    }

    // Status checking methods
    public function isPending(): bool
    {
        return $this->status === PurchaseOrderStatus::PENDING;
    }

    public function isPartial(): bool
    {
        return $this->status === PurchaseOrderStatus::PARTIAL;
    }

    public function isCompleted(): bool
    {
        return $this->status === PurchaseOrderStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PurchaseOrderStatus::CANCELLED;
    }

    // Attributes
    protected function totalQuantityOrdered(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('quantity_ordered'),
        );
    }

    protected function totalQuantityReceived(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->goodsReceipts()
                ->where('status', PurchaseOrderStatus::COMPLETED)
                ->get()
                ->flatMap->details
                ->sum('quantity_accepted'),
        );
    }

    protected function receivedPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $ordered = $this->total_quantity_ordered;
                if ($ordered <= 0) {
                    return 0;
                }

                return round(($this->total_quantity_received / $ordered) * 100, 2);
            },
        );
    }

    // Methods
    public function calculateTotalAmount(): void
    {
        $total = $this->details->sum(function ($detail) {
            return $detail->quantity_ordered * $detail->price;
        });

        $this->update(['total_amount' => $total]);
    }

    public function updateStatus(): void
    {
        if ($this->isCancelled()) {
            return;
        }

        $totalOrdered = $this->total_quantity_ordered;
        $totalReceived = $this->total_quantity_received;

        if ($totalReceived <= 0) {
            $this->update(['status' => PurchaseOrderStatus::PENDING]);
        } elseif ($totalReceived >= $totalOrdered) {
            $this->update(['status' => PurchaseOrderStatus::COMPLETED]);
        } else {
            $this->update(['status' => PurchaseOrderStatus::PARTIAL]);
        }

        // Optionally, you might want to notify relevant users about the status update
        Notification::make()
            ->title('Purchase Order Status Updated')
            ->body("Purchase Order {$this->po_number} status has been updated to {$this->status->value}.")
            ->info()
            ->sendToDatabase($this->supplier->users);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => PurchaseOrderStatus::CANCELLED]);

        // Optionally, you might want to notify relevant users about the cancellation
        Notification::make()
            ->title('Purchase Order Cancelled')
            ->body("Purchase Order {$this->po_number} has been cancelled.")
            ->danger()
            ->sendToDatabase($this->supplier->users);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (empty($purchaseOrder->po_number)) {
                $purchaseOrder->po_number = self::generatePONumber();
            }
        });
    }

    protected static function generatePONumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now())->count() + 1;

        return 'PO-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
