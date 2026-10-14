<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductIllustration;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:64', Rule::unique(Product::class)->ignore($product)],
            'name' => ['required', 'array:uz,ru,en'],
            'name.uz' => ['required', 'string', 'max:255'],
            'name.ru' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array:uz,ru,en'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:100000'],
            'box_quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'consumer_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'reseller_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'illustration' => ['nullable', Rule::enum(ProductIllustration::class)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * Accepts amounts typed with spaces or separators, e.g. "185 000".
     */
    private function money(string $key): ?int
    {
        return $this->filled($key) ? (int) preg_replace('/\D/', '', (string) $this->input($key)) : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'consumer_price' => $this->money('consumer_price'),
            'reseller_price' => $this->money('reseller_price'),
            'sort_order' => $this->input('sort_order', 0) ?? 0,
            'remove_image' => $this->boolean('remove_image'),
            'is_featured' => $this->boolean('is_featured'),
            'is_new' => $this->boolean('is_new'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
