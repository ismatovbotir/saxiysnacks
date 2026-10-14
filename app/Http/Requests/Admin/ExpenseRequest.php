<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'amount' => ['required', 'integer', 'min:1', 'max:10000000000'],
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
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
            'spent_on' => $this->input('spent_on') ?: today()->toDateString(),
        ]);
    }
}
