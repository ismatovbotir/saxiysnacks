<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Enums\ProductIllustration;
use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id', 'sku', 'gtin', 'class_code', 'package_code', 'box_package_code', 'name', 'description', 'weight_grams', 'box_quantity', 'consumer_price', 'consumer_min_quantity', 'consumer_quantity_step', 'reseller_price', 'reseller_min_quantity', 'reseller_quantity_step',
    'illustration', 'image_path', 'is_featured', 'is_new', 'is_active', 'sort_order',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use GeneratesSlug, HasFactory, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'weight_grams' => 'integer',
            'box_quantity' => 'integer',
            'consumer_price' => 'integer',
            'reseller_price' => 'integer',
            'consumer_min_quantity' => 'integer',
            'consumer_quantity_step' => 'integer',
            'reseller_min_quantity' => 'integer',
            'reseller_quantity_step' => 'integer',
            'illustration' => ProductIllustration::class,
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return $this->translate('name', 'en').' '.$this->weight_grams.'g';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Adds "stock_balance" (pieces in the warehouse) to each product.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withStockBalance(Builder $query): void
    {
        $query->withSum('stockMovements as stock_balance', 'quantity');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null);
    }

    /**
     * The price per order unit for a client type: per piece for consumers, per package for resellers.
     * Resellers fall back to the consumer piece price × pieces per package when no reseller price is set.
     */
    public function priceFor(ClientType $type): ?int
    {
        $price = $type === ClientType::Reseller
            ? ($this->reseller_price ?? $this->consumerPackagePrice())
            : $this->consumer_price;

        return $price > 0 ? $price : null;
    }

    /**
     * What a package costs at consumer prices; unknown when the package size is not set.
     */
    public function consumerPackagePrice(): ?int
    {
        return $this->consumer_price !== null && $this->box_quantity
            ? $this->consumer_price * $this->box_quantity
            : null;
    }

    /**
     * Fewest units (pieces for consumers, packages for resellers) a customer of this type can order.
     */
    public function minQuantityFor(ClientType $type): int
    {
        $minimum = $type === ClientType::Reseller ? $this->reseller_min_quantity : $this->consumer_min_quantity;

        return max(1, (int) $minimum);
    }

    /**
     * Units added per step above the minimum (e.g. min 5, step 5 → 5, 10, 15…).
     */
    public function quantityStepFor(ClientType $type): int
    {
        $step = $type === ClientType::Reseller ? $this->reseller_quantity_step : $this->consumer_quantity_step;

        return max(1, (int) $step);
    }

    public function allowsQuantity(ClientType $type, int $quantity): bool
    {
        $minimum = $this->minQuantityFor($type);

        return $quantity >= $minimum && ($quantity - $minimum) % $this->quantityStepFor($type) === 0;
    }

    /**
     * The nearest allowed quantity at or above the requested one.
     */
    public function allowedQuantity(ClientType $type, int $quantity): int
    {
        $minimum = $this->minQuantityFor($type);
        $step = $this->quantityStepFor($type);

        return $quantity <= $minimum ? $minimum : $minimum + (int) ceil(($quantity - $minimum) / $step) * $step;
    }

    public function isOrderableBy(ClientType $type): bool
    {
        return $this->is_active && $this->priceFor($type) !== null;
    }

    /**
     * Weight label in the "30±5" format printed on the packs.
     */
    public function weightLabel(): string
    {
        $tolerance = $this->weight_grams <= 10 ? 2 : 5;

        return "{$this->weight_grams}±{$tolerance}";
    }
}
