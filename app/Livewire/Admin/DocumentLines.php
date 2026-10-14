<?php

namespace App\Livewire\Admin;

use App\Enums\ClientType;
use App\Enums\OrderUnit;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Line editor of a warehouse document, MoySklad-style: products are added through a search box ("+ Add item"),
 * quantities and prices are edited in the table, and pieces and sums update live. Inputs carry the form names
 * (lines[n][...]), so the surrounding form submits them. A read-only table is shown for posted documents.
 */
class DocumentLines extends Component
{
    /**
     * The document type's value (Livewire keeps scalar state; mount() receives the enum).
     */
    public string $kind = '';

    /**
     * @var array<int, array{product_id: string, unit: string, quantity: string, price: string}>
     */
    public array $lines = [];

    /**
     * @var array<string, array<int, string>>
     */
    public array $fieldErrors = [];

    public string $search = '';

    public bool $readOnly = false;

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, array<int, string>>  $fieldErrors
     */
    public function mount(StockDocumentType $type, array $lines = [], array $fieldErrors = [], bool $readOnly = false): void
    {
        $this->readOnly = $readOnly;
        $this->kind = $type->value;
        $this->lines = collect($lines)->map(fn (array $line): array => [
            'product_id' => (string) ($line['product_id'] ?? ''),
            'unit' => (string) ($line['unit'] ?? OrderUnit::Piece->value),
            'quantity' => (string) ($line['quantity'] ?? ''),
            'price' => (string) ($line['price'] ?? ''),
        ])->values()->all();
        $this->fieldErrors = $fieldErrors;
    }

    /**
     * Add a product picked in the search; adding the same product again just raises its quantity.
     */
    public function addProduct(int $productId): void
    {
        $product = $this->products[$productId] ?? null;

        if ($product === null || $this->readOnly) {
            return;
        }

        $existing = collect($this->lines)->search(fn (array $line): bool => (int) $line['product_id'] === $productId);

        if ($existing !== false) {
            $this->lines[$existing]['quantity'] = (string) ((int) $this->lines[$existing]['quantity'] + 1);
        } else {
            $unit = $this->documentType === StockDocumentType::Sale || ! $product->box_quantity ? OrderUnit::Piece : OrderUnit::Package;
            $this->lines[] = [
                'product_id' => (string) $productId,
                'unit' => $unit->value,
                'quantity' => '1',
                'price' => $this->documentType === StockDocumentType::Sale ? (string) ($product->priceFor(ClientType::Consumer) ?? '') : '',
            ];
        }

        $this->search = '';
    }

    /**
     * Enter in the search box adds the first match.
     */
    public function addFirstMatch(): void
    {
        if ($first = $this->matches->first()) {
            $this->addProduct($first->id);
        }
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    /**
     * Products matching the search box: name in any language, SKU or GTIN.
     *
     * @return Collection<int, Product>
     */
    #[Computed]
    public function matches(): Collection
    {
        $term = mb_strtolower(trim($this->search));

        if ($term === '') {
            return collect();
        }

        return $this->products
            ->filter(fn (Product $product): bool => str_contains(mb_strtolower(implode(' ', [...array_values((array) $product->name), $product->sku, $product->gtin, $product->weight_grams.'g'])), $term))
            ->take(8)
            ->values();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return Product::query()->with('category')->withStockBalance()->ordered()->get()->keyBy('id');
    }

    #[Computed]
    public function documentType(): StockDocumentType
    {
        return StockDocumentType::from($this->kind);
    }

    /**
     * @param  array{product_id: string, unit: string, quantity: string, price: string}  $line
     */
    public function pieces(array $line): ?int
    {
        $product = $this->products[(int) $line['product_id']] ?? null;
        $quantity = (int) $line['quantity'];

        if ($product === null || $quantity === 0) {
            return null;
        }

        return $line['unit'] === OrderUnit::Package->value ? ($product->box_quantity ? $quantity * $product->box_quantity : null) : $quantity;
    }

    /**
     * @param  array{product_id: string, unit: string, quantity: string, price: string}  $line
     */
    public function amount(array $line): int
    {
        return (int) $line['quantity'] * (int) preg_replace('/\D/', '', $line['price']);
    }

    #[Computed]
    public function totalPieces(): int
    {
        return (int) collect($this->lines)->sum(fn (array $line): int => (int) $this->pieces($line));
    }

    #[Computed]
    public function total(): string
    {
        return Money::format(abs((int) collect($this->lines)->sum(fn (array $line): int => $this->amount($line))));
    }

    public function error(string $field): ?string
    {
        return $this->fieldErrors[$field][0] ?? null;
    }

    public function render(): View
    {
        return view('livewire.admin.document-lines');
    }
}
