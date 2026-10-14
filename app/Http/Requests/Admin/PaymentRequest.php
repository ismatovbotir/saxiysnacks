<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:10000000000'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Accepts amounts typed with spaces, e.g. "1 250 000".
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => $this->filled('amount') ? (int) preg_replace('/\D/', '', (string) $this->input('amount')) : null,
            'paid_on' => $this->input('paid_on') ?: today()->toDateString(),
        ]);
    }
}
