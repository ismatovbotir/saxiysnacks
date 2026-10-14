<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockDocument;
use Illuminate\Validation\ValidationException;

class SyncOrderStock
{
    public function __construct(
        private SaveStockDocument $saveDocument,
        private PostStockDocument $postDocument,
        private CancelStockDocument $cancelDocument,
    ) {}

    /**
     * Keep the warehouse in step with an order: shipping (or completing) it creates and posts its sales invoice;
     * cancelling it, or moving it back before shipping, cancels that invoice. The goods have physically left
     * by then, so the invoice may take stock below zero.
     */
    public function handle(Order $order): void
    {
        $order->loadMissing(['items', 'saleDocument', 'client']);
        $shouldBeShipped = in_array($order->status, [OrderStatus::Shipped, OrderStatus::Completed], true);
        $invoice = $order->saleDocument;

        if ($shouldBeShipped && $invoice?->status !== StockDocumentStatus::Posted) {
            $this->ship($order, $invoice);
        }

        if (! $shouldBeShipped && $invoice?->isPosted()) {
            $this->cancelDocument->handle($invoice);
        }
    }

    private function ship(Order $order, ?StockDocument $previousInvoice): void
    {
        $uncountable = $order->items->first(fn (OrderItem $item): bool => $item->product_id === null || $item->pieces() === null);

        if ($uncountable !== null) {
            throw ValidationException::withMessages(['status' => __(':product cannot be taken out of stock: the product was deleted or its package size is unknown.', ['product' => $uncountable->product_name])]);
        }

        $lines = $order->items
            ->map(fn (OrderItem $item): array => [
                'product_id' => $item->product_id,
                'unit' => $order->unit()->value,
                'quantity' => $item->quantity,
                'pieces' => $item->pieces(),
                'price' => $item->unit_price,
            ])
            ->values()
            ->all();

        if ($lines === []) {
            return;
        }

        // A re-shipped order gets a new invoice; the cancelled one stays in the list for history.
        $previousInvoice?->update(['order_id' => null, 'note' => trim($previousInvoice->note.' · '.__('replaced'))]);

        $document = $this->saveDocument->handle(StockDocumentType::Sale, [
            'document_date' => today()->toDateString(),
            'counterparty' => $order->customerName(),
            'telegram_client_id' => $order->telegram_client_id,
            'note' => $order->number(),
        ], $lines, auth()->user());

        $document->update(['order_id' => $order->id]);

        $this->postDocument->handle($document, auth()->user(), allowNegativeStock: true);
    }
}
