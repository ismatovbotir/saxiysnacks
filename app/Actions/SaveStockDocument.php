<?php

namespace App\Actions;

use App\Enums\OrderUnit;
use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveStockDocument
{
    /**
     * Create or update a draft document and replace its lines. Package quantities are converted to pieces with the
     * product's pieces per package; prices are per chosen unit.
     *
     * @param  array{document_date: string, counterparty?: string|null, telegram_client_id?: int|null, note?: string|null}  $header
     * @param  array<int, array{product_id: int, unit: string, quantity: int, price?: int|null, pieces?: int}>  $lines  "pieces" overrides the conversion (orders keep the package size they were sold with)
     */
    public function handle(StockDocumentType $type, array $header, array $lines, ?User $user = null, ?StockDocument $document = null): StockDocument
    {
        if ($document !== null && ! $document->isDraft()) {
            throw ValidationException::withMessages(['document' => __('Only drafts can be changed.')]);
        }

        $products = Product::query()->whereKey(collect($lines)->pluck('product_id'))->get()->keyBy('id');

        $rows = collect($lines)->values()->map(function (array $line, int $index) use ($products, $type): array {
            $product = $products[$line['product_id']];
            $unit = OrderUnit::from($line['unit']);

            if ($unit === OrderUnit::Package && ! isset($line['pieces']) && ! $product->box_quantity) {
                throw ValidationException::withMessages([
                    "lines.{$index}.unit" => __(':product has no package size; enter pieces.', ['product' => $product->translate('name', app()->getLocale())]),
                ]);
            }

            $price = $type->hasPrices() ? ($line['price'] ?? null) : null;

            return [
                'product_id' => $product->id,
                'unit' => $unit,
                'quantity' => (int) $line['quantity'],
                'pieces' => $line['pieces'] ?? (int) $line['quantity'] * ($unit === OrderUnit::Package ? $product->box_quantity : 1),
                'price' => $price,
                'amount' => (int) $line['quantity'] * (int) $price,
            ];
        });

        return DB::transaction(function () use ($type, $header, $rows, $user, $document): StockDocument {
            $document ??= new StockDocument([
                'type' => $type,
                'number' => StockDocument::nextNumber($type),
                'status' => StockDocumentStatus::Draft,
                'created_by' => $user?->id,
            ]);

            $document->fill([
                'document_date' => $header['document_date'],
                'counterparty' => $header['counterparty'] ?? null,
                'telegram_client_id' => $type === StockDocumentType::Sale ? ($header['telegram_client_id'] ?? null) : null,
                'note' => $header['note'] ?? null,
                'total' => abs($rows->sum('amount')),
            ])->save();

            $document->lines()->delete();
            $document->lines()->createMany($rows->all());

            return $document;
        });
    }
}
