<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderUnit;
use App\Enums\StockDocumentType;
use App\Models\StockDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockDocumentRequest extends FormRequest
{
    /**
     * Receipt and sale quantities are positive; correction lines may be negative (shortage) but not zero.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $type = $this->documentType();

        return [
            'document_date' => ['required', 'date', 'before_or_equal:today'],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'telegram_client_id' => ['nullable', 'integer', 'exists:telegram_clients,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1', 'max:200'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.unit' => ['required', Rule::enum(OrderUnit::class)],
            'lines.*.quantity' => ['required', 'integer', 'between:-1000000,1000000', $type === StockDocumentType::Correction ? 'not_in:0' : 'min:1'],
            'lines.*.price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ];
    }

    public function documentType(): ?StockDocumentType
    {
        /** @var StockDocument|null $document */
        $document = $this->route('document');

        return $document?->type ?? StockDocumentType::tryFrom((string) $this->route('documentType'));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'lines.*.product_id' => __('Product'),
            'lines.*.quantity' => __('Quantity'),
            'lines.*.price' => __('Price'),
        ];
    }

    /**
     * Empty rows are dropped; prices may be typed with spaces ("185 000").
     */
    protected function prepareForValidation(): void
    {
        $lines = collect((array) $this->input('lines', []))
            ->filter(fn (mixed $line): bool => is_array($line) && filled($line['product_id'] ?? null))
            ->map(fn (array $line): array => [...$line, 'price' => filled($line['price'] ?? null) ? (int) preg_replace('/\D/', '', (string) $line['price']) : null])
            ->values()
            ->all();

        $this->merge(['lines' => $lines, 'document_date' => $this->input('document_date') ?: today()->toDateString()]);
    }
}
