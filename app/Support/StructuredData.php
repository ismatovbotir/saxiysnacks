<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Builds schema.org JSON-LD nodes used by Google rich results and AI search engines.
 */
class StructuredData
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $locale = app()->getLocale();

        return array_filter([
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $this->settings->get('brand_name'),
            'legalName' => $this->settings->text('legal_name', $locale) ?: null,
            'description' => $this->settings->text('tagline', $locale) ?: null,
            'url' => url('/'),
            'logo' => asset('logo.svg'),
            'image' => $this->settings->ogImageUrl(),
            'foundingDate' => (string) $this->settings->get('founded_year'),
            'email' => $this->settings->get('email'),
            'telephone' => $this->settings->primaryPhone(),
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $this->settings->text('address', $locale),
                'addressRegion' => 'Tashkent Region',
                'addressCountry' => 'UZ',
            ]),
            'contactPoint' => collect($this->settings->get('phones'))->map(fn (string $phone): array => [
                '@type' => 'ContactPoint',
                'telephone' => $phone,
                'contactType' => 'sales',
                'areaServed' => 'UZ',
                'availableLanguage' => ['uz', 'ru', 'en'],
            ])->all(),
            'sameAs' => array_values($this->settings->socialLinks()) ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => $this->settings->get('brand_name'),
            'url' => Storefront::route('home'),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => url('/').'#organization'],
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $crumbs  [name, url] pairs
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $crumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ])->all(),
        ];
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public function itemList(Collection $products): array
    {
        return [
            '@type' => 'ItemList',
            'itemListElement' => $products->values()->map(fn (Product $product, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => Storefront::route('product', [$product->category, $product]),
                'name' => $product->translate('name'),
            ])->all(),
        ];
    }

    /**
     * Prices are B2B-only, so no Offer is published.
     *
     * @return array<string, mixed>
     */
    public function product(Product $product, Category $category): array
    {
        return array_filter([
            '@type' => 'Product',
            '@id' => Storefront::route('product', [$category, $product]).'#product',
            'name' => $product->translate('name').' '.$product->weight_grams.' '.__('site.g'),
            'description' => $product->translate('description') ?: null,
            'image' => $product->image_url,
            'sku' => $product->sku ?: $product->slug,
            'gtin' => $product->gtin,
            'category' => $category->translate('name'),
            'brand' => ['@type' => 'Brand', 'name' => $this->settings->get('brand_name')],
            'manufacturer' => ['@id' => url('/').'#organization'],
            'countryOfOrigin' => 'UZ',
            'weight' => ['@type' => 'QuantitativeValue', 'value' => $product->weight_grams, 'unitCode' => 'GRM'],
            'url' => Storefront::route('product', [$category, $product]),
        ]);
    }
}
