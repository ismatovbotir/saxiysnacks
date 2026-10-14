<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Editable table of a product's order units inside the product form: a piece (multiplier fixed at 1, consumer price)
 * and a package (editable pieces per package, reseller price). The package price is entered per piece and the table shows
 * what the whole package costs; the request multiplies it out on save. Inputs keep their form names, so the surrounding
 * form still submits them; Livewire only keeps the live hints (package price, allowed quantities) in sync.
 */
class ProductUnits extends Component
{
    public string $pieceCode = '';

    public string $piecePrice = '';

    public string $pieceMin = '1';

    public string $pieceStep = '1';

    public string $packageCode = '';

    public string $packageMultiplier = '';

    /**
     * Reseller price per piece; the package price is this × pieces per package.
     */
    public string $packagePiecePrice = '';

    public string $packageMin = '';

    public string $packageStep = '';

    /**
     * @var array<string, array<int, string>>
     */
    public array $fieldErrors = [];

    /**
     * @param  array<string, mixed>  $old  input flashed back after a failed save
     * @param  array<string, array<int, string>>  $fieldErrors
     */
    public function mount(Product $product, array $old = [], array $fieldErrors = []): void
    {
        $value = fn (string $key, mixed $default = null): string => (string) (array_key_exists($key, $old) ? $old[$key] : ($product->{$key} ?? $default));

        $this->pieceCode = $value('package_code');
        $this->piecePrice = $value('consumer_price');
        $this->pieceMin = $value('consumer_min_quantity', 1);
        $this->pieceStep = $value('consumer_quantity_step', 1);
        $this->packageCode = $value('box_package_code');
        $this->packageMultiplier = $value('box_quantity');
        $this->packagePiecePrice = array_key_exists('reseller_piece_price', $old)
            ? (string) $old['reseller_piece_price']
            : $this->piecePriceOf($product->reseller_price, $product->box_quantity);
        $this->packageMin = $value('reseller_min_quantity');
        $this->packageStep = $value('reseller_quantity_step');
        $this->fieldErrors = $fieldErrors;
    }

    /**
     * What a whole package costs: the reseller piece price × pieces per package, or the consumer piece price
     * when the reseller price is left empty.
     *
     * @return array{amount: string, fallback: bool}|null
     */
    #[Computed]
    public function packagePrice(): ?array
    {
        $multiplier = $this->digits($this->packageMultiplier);
        $piecePrice = $this->digits($this->packagePiecePrice);
        $fallback = $piecePrice === null;
        $piecePrice ??= $this->digits($this->piecePrice);

        return $multiplier && $piecePrice !== null ? ['amount' => Money::format($piecePrice * $multiplier), 'fallback' => $fallback] : null;
    }

    /**
     * Pieces in each allowed package quantity, e.g. "40, 80, 120 …".
     */
    #[Computed]
    public function packagePieces(): ?string
    {
        $multiplier = $this->digits($this->packageMultiplier);

        return $multiplier ? $this->preview($this->packageMin, $this->packageStep, $multiplier) : null;
    }

    public function preview(string $minimum, string $step, int $multiplier = 1): string
    {
        $minimum = max(1, $this->digits($minimum) ?? 1);
        $step = max(1, $this->digits($step) ?? 1);

        return collect([0, 1, 2])->map(fn (int $index): string => number_format(($minimum + $index * $step) * $multiplier, 0, '.', ' '))->implode(', ').' …';
    }

    public function error(string $field): ?string
    {
        return $this->fieldErrors[$field][0] ?? null;
    }

    /**
     * The per-piece form of a stored package price (rounded if it does not divide evenly).
     */
    private function piecePriceOf(?int $packagePrice, ?int $multiplier): string
    {
        if ($packagePrice === null) {
            return '';
        }

        return (string) ($multiplier ? (int) round($packagePrice / $multiplier) : $packagePrice);
    }

    private function digits(string $value): ?int
    {
        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? null : (int) $digits;
    }

    public function render(): View
    {
        return view('livewire.admin.product-units');
    }
}
