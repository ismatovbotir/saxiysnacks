<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class PlaceWebsiteOrderRequest extends FormRequest
{
    /**
     * Guests identify themselves with a name and phone only; the cart sends product slugs and piece quantities.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{9,20}$/'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.slug' => ['required', 'string', 'distinct', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.errName'),
            'phone.required' => __('site.errPhone'),
            'phone.regex' => __('site.errPhone'),
            'items.required' => __('site.empty'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only(['name', 'phone', 'comment']))->map(fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)->all());
    }
}
