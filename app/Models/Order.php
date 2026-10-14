<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\OrderUnit;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'source', 'telegram_client_id', 'customer_name', 'customer_phone', 'status', 'price_type', 'total', 'delivery_latitude', 'delivery_longitude',
    'delivery_address', 'comment', 'admin_message_id',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => OrderSource::class,
            'status' => OrderStatus::class,
            'price_type' => ClientType::class,
            'total' => 'integer',
            'delivery_latitude' => 'float',
            'delivery_longitude' => 'float',
            'admin_message_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<TelegramClient, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class, 'telegram_client_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->chaperone();
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * The sales invoice created when the order shipped.
     *
     * @return HasOne<StockDocument, $this>
     */
    public function saleDocument(): HasOne
    {
        return $this->hasOne(StockDocument::class);
    }

    /**
     * What is still owed on the order; cancelled orders owe nothing. Needs payments loaded or summed as "paid".
     */
    public function amountDue(): int
    {
        if ($this->status === OrderStatus::Cancelled) {
            return 0;
        }

        $paid = $this->paid ?? $this->payments->sum('amount');

        return max(0, $this->total - (int) $paid);
    }

    /**
     * Unit the quantities are counted in, fixed by the price type the order was placed at.
     */
    public function unit(): OrderUnit
    {
        return $this->price_type->orderUnit();
    }

    public function isFromWebsite(): bool
    {
        return $this->source === OrderSource::Website;
    }

    /**
     * Name to show for the order: the Telegram customer, or the name a website guest typed.
     */
    public function customerName(): string
    {
        return $this->client?->displayName() ?? (string) $this->customer_name;
    }

    public function customerPhone(): string
    {
        return $this->client?->phone ?? (string) $this->customer_phone;
    }

    public function hasDeliveryLocation(): bool
    {
        return $this->delivery_latitude !== null && $this->delivery_longitude !== null;
    }

    public function number(): string
    {
        return 'SX-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
