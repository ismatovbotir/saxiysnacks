<?php

namespace App\View\Composers;

use App\Enums\ClientType;
use App\Models\Category;
use App\Models\Product;
use App\Support\SiteSettings;
use Illuminate\View\View;

/**
 * Shared data for the public site pages and layout: navigation, contacts and the product list the inquiry cart needs.
 */
class StorefrontComposer
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $data = [];

    public function __construct(private SiteSettings $settings) {}

    public function compose(View $view): void
    {
        $view->with($this->data[app()->getLocale()] ??= $this->data());
    }

    /**
     * @return array<string, mixed>
     */
    private function data(): array
    {
        $locale = app()->getLocale();

        return [
            'site' => $this->settings,
            'locale' => $locale,
            'navCategories' => Category::query()->active()->ordered()->get(),
            'cartProducts' => Product::query()->active()->ordered()->get()->mapWithKeys(fn (Product $product): array => [
                $product->slug => [
                    'name' => $product->translate('name', $locale),
                    'w' => $product->weightLabel(),
                    'box' => $product->box_quantity,
                    'min' => $product->minQuantityFor(ClientType::Consumer),
                    'step' => $product->quantityStepFor(ClientType::Consumer),
                ],
            ]),
            'telegramBotUrl' => $this->settings->telegramBotUrl(),
        ];
    }
}
