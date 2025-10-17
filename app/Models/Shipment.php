<?php

// declare(strict_types=1);

namespace App\Models;

use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Policies\ShipmentPolicy;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(ShipmentPolicy::class)]
class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_number',
        'purchase_order_id',
        'supplier_id',
        'delivery_order_number',
        'shipping_date',
        'estimated_arrival_date',
        'status',
        'do_scan_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_date' => 'date',
            'estimated_arrival_date' => 'date',
            'status' => ShipmentStatus::class,
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt(): HasOne
    {
        return $this->hasOne(GoodsReceipt::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ShipmentDetail::class);
    }

    // ==========================================
    // STATUS CHECKING METHODS
    // ==========================================

    public function isDraft(): bool
    {
        return $this->status === ShipmentStatus::DRAFT;
    }

    public function isShipped(): bool
    {
        return $this->status === ShipmentStatus::SHIPPED;
    }

    public function isArrived(): bool
    {
        return $this->status === ShipmentStatus::ARRIVED;
    }

    public function isProcessed(): bool
    {
        return $this->status === ShipmentStatus::PROCESSED;
    }

    // ==========================================
    // STATUS TRANSITION METHODS
    // ==========================================

    public function markAsShipped(): void
    {
        $this->update(['status' => ShipmentStatus::SHIPPED]);

        // Send notification to admin that shipment has been shipped
        Notification::make()
            ->title('Shipment Shipped')
            ->body("Shipment {$this->shipment_number} has been marked as shipped.")
            ->success()
            ->viewData(['shipment_id' => $this->id])
            ->icon('heroicon-o-truck')
            ->actions([
                Action::make('view_shipment')
                    ->label('View Shipment')
                    ->url(route('filament.app.resources.shipments.view', ['record' => $this->id])),
            ])
            ->sendToDatabase(User::where('role', UserRole::Admin)->orWhere('role', UserRole::Checker)->get());
    }

    public function markAsArrived(): void
    {
        $this->update(['status' => ShipmentStatus::ARRIVED]);
        // Send notification to admin that shipment has been shipped
        Notification::make()
            ->title('Shipment Arrived')
            ->body("Shipment {$this->shipment_number} has been marked as arrived.")
            ->success()
            ->actions([
                Action::make('view_shipment')
                    ->label('View Shipment')
                    ->url(route('filament.app.resources.shipments.view', ['record' => $this->id])),

                Action::make('create_goods_receipt')
                    ->label('Create Goods Receipt')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(route('filament.app.resources.goods-receipts.create', [
                        'shipment_id' => $this->id,
                        'purchase_order_id' => $this->purchase_order_id,
                    ])),
            ])
            ->icon('heroicon-o-check-circle')
            ->sendToDatabase(User::where('role', UserRole::Admin)->orWhere('role', UserRole::Checker)->get());
    }

    public function markAsProcessed(): void
    {
        $this->update(['status' => ShipmentStatus::PROCESSED]);

        // Send notification to admin that shipment has been shipped
        Notification::make()
            ->title('Shipment Processed')
            ->body("Shipment {$this->shipment_number} has been marked as processed.")
            ->info()
            ->icon(Heroicon::OutlinedArchiveBox)
            ->actions([
                Action::make('view_shipment')
                    ->label('View Shipment')
                    ->url(route('filament.app.resources.shipments.view', ['record' => $this->id])),
            ])
            ->sendToDatabase(User::where('role', UserRole::Admin)->orWhere('role', UserRole::Checker)->get());
    }

    // ==========================================
    // ATTRIBUTES
    // ==========================================

    protected function totalQuantityShipped(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('quantity_shipped'),
        );
    }

    protected function totalItems(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->count(),
        );
    }

    protected function isEditable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === ShipmentStatus::DRAFT,
        );
    }

    protected function canBeShipped(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === ShipmentStatus::DRAFT && $this->details->count() > 0,
        );
    }

    // ==========================================
    // BOOT METHOD
    // ==========================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Shipment $shipment): void {
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = self::generateShipmentNumber();
            }

            if ($shipment->status === null) {
                $shipment->status = ShipmentStatus::DRAFT;
            }
        });
    }

    protected static function generateShipmentNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now())->count() + 1;

        return 'ASN-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
