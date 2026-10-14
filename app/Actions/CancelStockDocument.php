<?php

namespace App\Actions;

use App\Enums\StockDocumentStatus;
use App\Models\StockDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelStockDocument
{
    /**
     * Cancel a document; a posted one loses its stock movements, so balances return to what they were.
     * The document itself stays in the list, marked cancelled.
     */
    public function handle(StockDocument $document): StockDocument
    {
        if ($document->status === StockDocumentStatus::Cancelled) {
            throw ValidationException::withMessages(['document' => __('This document is already cancelled.')]);
        }

        return DB::transaction(function () use ($document): StockDocument {
            $document->movements()->delete();
            $document->update(['status' => StockDocumentStatus::Cancelled]);

            return $document;
        });
    }
}
