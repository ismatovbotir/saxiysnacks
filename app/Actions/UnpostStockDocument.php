<?php

namespace App\Actions;

use App\Enums\StockDocumentStatus;
use App\Models\StockDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UnpostStockDocument
{
    /**
     * Take a posted document back to draft (MoySklad's "unpost"): its stock movements are removed and it can be
     * edited or deleted again. Invoices of orders follow their order's status instead.
     */
    public function handle(StockDocument $document): StockDocument
    {
        if (! $document->isPosted()) {
            throw ValidationException::withMessages(['document' => __('Only posted documents can be unposted.')]);
        }

        if ($document->order_id !== null) {
            throw ValidationException::withMessages(['document' => __('This invoice belongs to order :number: change the order status instead.', ['number' => $document->order->number()])]);
        }

        return DB::transaction(function () use ($document): StockDocument {
            $document->movements()->delete();
            $document->update(['status' => StockDocumentStatus::Draft, 'posted_by' => null, 'posted_at' => null]);

            return $document;
        });
    }
}
