<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'telegram_client_id', 'status', 'price_type', 'total', 'delivery_latitude', 'delivery_longitude',
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
        return $this->hasMany(OrderItem::class);
    }

    public function number(): string
    {
        return 'SX-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
