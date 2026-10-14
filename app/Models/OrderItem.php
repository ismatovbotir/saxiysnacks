<?php

namespace App\Models;

use App\Enums\OrderUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'product_name', 'weight_grams', 'box_quantity', 'quantity', 'unit_price', 'line_total'])]
class OrderItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_grams' => 'integer',
            'box_quantity' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'line_total' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Pieces on this line: the quantity itself for piece orders, packages × pieces per package otherwise.
     */
    public function pieces(): ?int
    {
        return match (true) {
            $this->order->unit() === OrderUnit::Piece => $this->quantity,
            $this->box_quantity !== null => $this->quantity * $this->box_quantity,
            default => null,
        };
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
