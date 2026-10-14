<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use Database\Factories\TelegramClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'telegram_id', 'telegram_username', 'language_code', 'first_name', 'last_name',
    'phone', 'company_name', 'vat_number', 'latitude', 'longitude', 'address', 'location_updated_at',
    'type', 'status', 'admin_message_id',
])]
class TelegramClient extends Model
{
    /** @use HasFactory<TelegramClientFactory> */
    use HasFactory, HasUuids;

    /**
     * New registrations start as active consumers.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'type' => 'consumer',
        'status' => 'active',
    ];

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
            'telegram_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'location_updated_at' => 'datetime',
            'type' => ClientType::class,
            'status' => ClientStatus::class,
            'admin_message_id' => 'integer',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * The customer's "My list": products they order regularly, with their usual number of boxes.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function savedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'saved_products')->withPivot('quantity')->withTimestamps();
    }

    /**
     * @return array<int, int> usual number of boxes keyed by product id
     */
    public function savedQuantities(): array
    {
        return $this->savedProducts()
            ->pluck('saved_products.quantity', 'products.id')
            ->map(fn (mixed $quantity): int => (int) $quantity)
            ->all();
    }

    public function isActive(): bool
    {
        return $this->status === ClientStatus::Active;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Save the client's delivery location; a null address keeps the existing address details.
     */
    public function moveTo(float $latitude, float $longitude, ?string $address = null): void
    {
        $this->forceFill([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address ?? $this->address,
            'location_updated_at' => now(),
        ])->save();
    }

    public function isReseller(): bool
    {
        return $this->type === ClientType::Reseller;
    }

    /**
     * Company name for registered businesses, otherwise the person's name.
     */
    public function displayName(): string
    {
        return $this->company_name ?: $this->fullName();
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
