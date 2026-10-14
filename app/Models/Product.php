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
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id', 'sku', 'name', 'description', 'weight_grams', 'box_quantity', 'consumer_price', 'reseller_price',
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
     * The price per box for a client type. Resellers fall back to the consumer price when no reseller price is set.
     */
    public function priceFor(ClientType $type): ?int
    {
        $price = $type === ClientType::Reseller
            ? ($this->reseller_price ?? $this->consumer_price)
            : $this->consumer_price;

        return $price > 0 ? $price : null;
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
