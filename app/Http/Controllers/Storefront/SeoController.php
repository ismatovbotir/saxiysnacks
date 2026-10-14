<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\SiteSettings;
use App\Support\Storefront;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * XML sitemap listing every public page in every language, with hreflang alternates.
     */
    public function sitemap(): Response
    {
        $categories = Category::query()->active()->ordered()->get();
        $products = Product::query()->active()->ordered()->with('category')->get();

        $pages = collect([
            ['home', [], $products->max('updated_at'), '1.0'],
            ['catalog', [], $products->max('updated_at'), '0.9'],
            ['contact', [], null, '0.5'],
        ])
            ->concat($categories->map(fn (Category $category): array => ['category', [$category], $category->updated_at, '0.8']))
            ->concat($products->map(fn (Product $product): array => ['product', [$product->category, $product], $product->updated_at, '0.7']))
            ->map(fn (array $page): array => [
                'alternates' => Storefront::alternates($page[0], $page[1]),
                'lastmod' => $page[2]?->toAtomString(),
                'priority' => $page[3],
            ]);

        return response(
            '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('seo.sitemap', ['pages' => $pages])->render(),
            200,
            ['Content-Type' => 'application/xml; charset=utf-8'],
        );
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /api/', 'Disallow: /webapp', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /**
     * Plain-text summary for AI assistants and LLM crawlers (https://llmstxt.org).
     */
    public function llms(SiteSettings $settings): Response
    {
        app()->setLocale('en');

        $categories = Category::query()
            ->active()
            ->ordered()
            ->with(['products' => fn ($query) => $query->where('is_active', true)->ordered()])
            ->get();

        return response()
            ->view('seo.llms', [
                'settings' => $settings,
                'categories' => $categories,
                'telegramBotUrl' => $settings->telegramBotUrl(),
            ], 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
