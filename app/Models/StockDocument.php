<?php

namespace App\Models;

use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use Database\Factories\StockDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'number', 'document_date', 'status', 'counterparty', 'telegram_client_id', 'order_id', 'total', 'note', 'created_by', 'posted_by', 'posted_at'])]
class StockDocument extends Model
{
    /** @use HasFactory<StockDocumentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StockDocumentType::class,
            'status' => StockDocumentStatus::class,
            'document_date' => 'date',
            'total' => 'integer',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<StockDocumentLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(StockDocumentLine::class)->chaperone('document');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * The customer picked for a sales invoice.
     *
     * @return BelongsTo<TelegramClient, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class, 'telegram_client_id');
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isDraft(): bool
    {
        return $this->status === StockDocumentStatus::Draft;
    }

    public function isPosted(): bool
    {
        return $this->status === StockDocumentStatus::Posted;
    }

    /**
     * Next number for a type, e.g. "IN-00012".
     */
    public static function nextNumber(StockDocumentType $type): string
    {
        $last = static::query()->where('type', $type)->lockForUpdate()->orderByDesc('id')->value('number');
        $sequence = $last ? ((int) substr($last, strlen($type->prefix()) + 1)) + 1 : 1;

        return $type->prefix().'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
