<?php

namespace App\Models;

use App\Enums\GoodsReceiptStatus;
use App\Enums\UserRole;
use App\Policies\GoodsReceiptPolicy;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(GoodsReceiptPolicy::class)]
class GoodsReceipt extends Model
{
    /** @use HasFactory<\Database\Factories\GoodReceiptFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'shipment_id',
        'delivery_order_number',
        'receipt_date',
        'received_by',
        'status',
        'pod_scan_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'datetime',
            'status' => GoodsReceiptStatus::class,
        ];
    }

    // Relationships
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class);
    }

    // Scopes
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', GoodsReceiptStatus::PENDING);
    }

    #[Scope]
    protected function verified(Builder $query): void
    {
        $query->where('status', GoodsReceiptStatus::VERIFIED);
    }

    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->where('status', GoodsReceiptStatus::COMPLETED);
    }

    // Status checking methods
    public function isPending(): bool
    {
        return $this->status === GoodsReceiptStatus::PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === GoodsReceiptStatus::VERIFIED;
    }

    public function isCompleted(): bool
    {
        return $this->status === GoodsReceiptStatus::COMPLETED;
    }

    // Attributes
    protected function totalQuantityReceived(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('quantity_received'),
        );
    }

    protected function totalQuantityAccepted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('quantity_accepted'),
        );
    }

    protected function totalQuantityRejected(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('quantity_rejected'),
        );
    }

    // Methods
    public function markAsVerified(): void
    {
        $this->update(['status' => GoodsReceiptStatus::VERIFIED]);

        // send notification to accounting that GRN has been verified
        Notification::make()
            ->title('Goods Receipt Verified')
            ->body("Goods Receipt {$this->grn_number} has been verified and is pending completion.")
            ->success()
            ->icon('heroicon-o-check-circle')
            ->sendToDatabase(User::where('role', UserRole::Admin)->orWhere('role', UserRole::Accounting)->get());
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => GoodsReceiptStatus::COMPLETED]);

        // Update stock for accepted quantities
        foreach ($this->details as $detail) {
            if ($detail->quantity_accepted > 0) {
                $detail->product->increaseStock($detail->quantity_accepted);
            }
        }

        // Update PO detail quantities received
        foreach ($this->details as $detail) {
            $poDetail = $this->purchaseOrder->details()
                ->where('product_id', $detail->product_id)
                ->first();

            if ($poDetail) {
                $poDetail->increment('quantity_received', $detail->quantity_accepted);
            }
        }

        // Update PO status
        $this->purchaseOrder->updateStatus();

        if ($this->shipment_id) {
            $this->shipment->markAsProcessed();
        }
        // send notification to supplier that GRN has been completed
        Notification::make()
            ->title('Goods Receipt Completed')
            ->body("Goods Receipt {$this->grn_number} has been completed.")
            ->success()
            ->icon(Heroicon::OutlinedArchiveBox)
            ->sendToDatabase($this->purchaseOrder->supplier->users);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($goodsReceipt) {
            if (empty($goodsReceipt->grn_number)) {
                $goodsReceipt->grn_number = self::generateGRNNumber();
            }

            if (empty($goodsReceipt->received_by)) {
                $goodsReceipt->received_by = auth()->id();
            }
        });
    }

    protected static function generateGRNNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now())->count() + 1;

        return 'GRN-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
