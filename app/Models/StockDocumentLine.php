<?php

namespace App\Models;

use App\Enums\OrderUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'unit', 'quantity', 'pieces', 'price', 'amount'])]
class StockDocumentLine extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit' => OrderUnit::class,
            'quantity' => 'integer',
            'pieces' => 'integer',
            'price' => 'integer',
            'amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<StockDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(StockDocument::class, 'stock_document_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
