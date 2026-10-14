<?php

namespace App\Http\Requests\WebApp;

use Illuminate\Foundation\Http\FormRequest;

class RegisterClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->attributes->get('telegram_client');

        return $client === null || $client->isActive();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{9,20}$/'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'regex:/^[0-9A-Za-z\-]{5,32}$/'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only(['first_name', 'last_name', 'phone', 'company_name', 'vat_number', 'address']))
            ->map(fn (mixed $value): mixed => is_string($value) ? (trim($value) ?: null) : $value)
            ->all());
    }
}
