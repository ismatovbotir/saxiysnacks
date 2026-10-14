<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductIllustration;
use App\Models\Product;
use App\Rules\Gtin;
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
            'gtin' => ['nullable', 'string', new Gtin, Rule::unique(Product::class)->ignore($product)],
            'class_code' => ['nullable', 'string', 'regex:/^\d{17}$/'],
            'package_code' => ['nullable', 'string', 'regex:/^\d{1,20}$/'],
            'box_package_code' => ['nullable', 'string', 'regex:/^\d{1,20}$/'],
            'name' => ['required', 'array:uz,ru,en'],
            'name.uz' => ['required', 'string', 'max:255'],
            'name.ru' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array:uz,ru,en'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:100000'],
            'box_quantity' => ['nullable', 'integer', 'min:1', 'max:100000', 'required_with:reseller_piece_price'],
            'consumer_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'reseller_piece_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'reseller_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'consumer_min_quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'consumer_quantity_step' => ['required', 'integer', 'min:1', 'max:10000'],
            'reseller_min_quantity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'reseller_quantity_step' => ['nullable', 'integer', 'min:1', 'max:10000'],
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
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'gtin.unique' => __('Another product already uses this GTIN.'),
            'class_code.regex' => __('The class code (MXIK / ИКПУ) must be exactly 17 digits.'),
            'package_code.regex' => __('The package code must contain digits only.'),
            'box_quantity.required_with' => __('Enter how many pieces are in a package to price it.'),
            'box_package_code.regex' => __('The package code must contain digits only.'),
        ];
    }

    /**
     * Codes are often pasted with spaces or dashes; keep digits only.
     */
    private function code(string $key): ?string
    {
        $digits = preg_replace('/[\s\-.]/', '', (string) $this->input($key));

        return $digits === '' ? null : $digits;
    }

    /**
     * Accepts amounts typed with spaces or separators, e.g. "185 000".
     */
    private function money(string $key): ?int
    {
        return $this->filled($key) ? (int) preg_replace('/\D/', '', (string) $this->input($key)) : null;
    }

    /**
     * The form asks resellers' price per piece; what is stored and charged is the package price
     * (price per piece × pieces per package). A package price sent directly is still accepted.
     */
    private function resellerPackagePrice(): ?int
    {
        if (! $this->exists('reseller_piece_price')) {
            return $this->money('reseller_price');
        }

        $piecePrice = $this->money('reseller_piece_price');
        $multiplier = (int) $this->input('box_quantity');

        return $piecePrice !== null && $multiplier > 0 ? $piecePrice * $multiplier : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gtin' => $this->code('gtin'),
            'class_code' => $this->code('class_code'),
            'package_code' => $this->code('package_code'),
            'box_package_code' => $this->code('box_package_code'),
            'consumer_price' => $this->money('consumer_price'),
            'reseller_piece_price' => $this->money('reseller_piece_price'),
            'reseller_price' => $this->resellerPackagePrice(),
            'sort_order' => $this->input('sort_order', 0) ?? 0,
            'consumer_min_quantity' => $this->filled('consumer_min_quantity') ? $this->input('consumer_min_quantity') : 1,
            'consumer_quantity_step' => $this->filled('consumer_quantity_step') ? $this->input('consumer_quantity_step') : 1,
            'remove_image' => $this->boolean('remove_image'),
            'is_featured' => $this->boolean('is_featured'),
            'is_new' => $this->boolean('is_new'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
