<?php

namespace App\Actions;

use App\Enums\StockDocumentStatus;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\StockDocumentLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostStockDocument
{
    /**
     * Post a draft: every line becomes a stock movement in pieces. Unless allowed (orders that already left),
     * posting may not take a product below zero.
     */
    public function handle(StockDocument $document, ?User $user = null, bool $allowNegativeStock = false): StockDocument
    {
        return DB::transaction(function () use ($document, $user, $allowNegativeStock): StockDocument {
            $document = StockDocument::query()->lockForUpdate()->findOrFail($document->getKey());
            $document->load('lines.product');

            if (! $document->isDraft()) {
                throw ValidationException::withMessages(['document' => __('Only drafts can be posted.')]);
            }

            if ($document->lines->isEmpty()) {
                throw ValidationException::withMessages(['document' => __('Add at least one line before posting.')]);
            }

            $direction = $document->type->direction();
            $signed = fn (StockDocumentLine $line): int => $direction === 0 ? $line->pieces : $direction * abs($line->pieces);

            if (! $allowNegativeStock) {
                $this->ensureEnoughStock($document, $signed);
            }

            $document->lines->each(fn (StockDocumentLine $line) => $document->movements()->create([
                'product_id' => $line->product_id,
                'type' => $document->type->movementType(),
                'quantity' => $signed($line),
                'order_id' => $document->order_id,
                'user_id' => $user?->id,
                'note' => $document->number,
            ]));

            $document->update(['status' => StockDocumentStatus::Posted, 'posted_by' => $user?->id, 'posted_at' => now()]);

            return $document;
        });
    }

    /**
     * @param  callable(StockDocumentLine): int  $signed
     */
    private function ensureEnoughStock(StockDocument $document, callable $signed): void
    {
        $changes = $document->lines->groupBy('product_id')->map(fn ($lines): int => $lines->sum($signed));

        // Lock the products so two documents posted at once cannot both use the same stock.
        Product::query()->whereKey($changes->keys())->lockForUpdate()->get();

        $balances = DB::table('stock_movements')
            ->whereIn('product_id', $changes->keys())
            ->groupBy('product_id')
            ->pluck(DB::raw('sum(quantity)'), 'product_id');

        foreach ($changes as $productId => $change) {
            $available = (int) ($balances[$productId] ?? 0);

            if ($change < 0 && $available + $change < 0) {
                $product = $document->lines->firstWhere('product_id', $productId)->product;

                throw ValidationException::withMessages(['document' => __('Not enough :product in stock: :available pcs available, :needed pcs needed.', [
                    'product' => $product->translate('name', app()->getLocale()),
                    'available' => $available,
                    'needed' => -$change,
                ])]);
            }
        }
    }
}
