<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create([
            'name' => ['uz' => "Yong'oqlar", 'ru' => 'Орехи', 'en' => 'Nuts'],
            'description' => ['uz' => "Qovurilgan mag'izlar.", 'ru' => 'Обжаренные ядра.', 'en' => 'Roasted kernels.'],
        ]);

        $this->product = Product::factory()->for($this->category)->create([
            'name' => ['uz' => 'Keshyu', 'ru' => 'Кешью', 'en' => 'Cashew'],
            'description' => ['uz' => null, 'ru' => 'Отборный кешью.', 'en' => 'Hand-picked cashew.'],
            'weight_grams' => 30,
            'box_quantity' => 40,
            'consumer_price' => 99000,
            'reseller_price' => 88000,
            'is_featured' => true,
        ]);
    }

    public function test_slugs_are_generated_from_the_english_name_and_stay_unique(): void
    {
        $this->assertSame('nuts', $this->category->slug);
        $this->assertSame('cashew-30g', $this->product->slug);

        $duplicate = Product::factory()->for($this->category)->create(['name' => ['uz' => 'Keshyu', 'ru' => 'Кешью', 'en' => 'Cashew'], 'weight_grams' => 30]);
        $this->assertSame('cashew-30g-2', $duplicate->slug);

        $russianOnly = Category::factory()->create(['name' => ['uz' => 'Pishloq', 'ru' => 'Сыр', 'en' => null]]);
        $this->assertSame('syr', $russianOnly->slug);
    }

    public function test_slugs_do_not_change_when_the_name_is_edited(): void
    {
        $this->product->update(['name' => ['uz' => 'Keshyu', 'ru' => 'Кешью', 'en' => 'Premium cashew']]);

        $this->assertSame('cashew-30g', $this->product->refresh()->slug);
    }

    public function test_home_page_is_server_rendered_in_each_language(): void
    {
        $this->get('/')->assertOk()->assertSee('<html lang="uz">', false)->assertSee('Keshyu');
        $this->get('/ru')->assertOk()->assertSee('<html lang="ru">', false)->assertSee('Кешью');
        $this->get('/en')->assertOk()->assertSee('<html lang="en">', false)->assertSee('Cashew');
        $this->get('/de')->assertNotFound();
    }

    public function test_product_page_has_seo_metadata_and_structured_data(): void
    {
        $url = route('localized.product', ['locale' => 'ru', 'category' => 'nuts', 'product' => 'cashew-30g']);

        $response = $this->get($url)->assertOk();

        $response->assertSee('<title>Кешью 30 г — оптом | SAXIY</title>', false)
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('<link rel="alternate" hreflang="uz" href="'.route('product', ['nuts', 'cashew-30g']).'">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="'.route('localized.product', ['en', 'nuts', 'cashew-30g']).'">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('">Кешью 30 г</h1>', false)
            ->assertSee('Отборный кешью.')
            ->assertDontSee('99000')
            ->assertDontSee('99 000')
            ->assertDontSee('88000')
            ->assertDontSee('88 000');

        $graph = $this->jsonLd($response->getContent());
        $types = array_column($graph, '@type');
        $this->assertSame(['Organization', 'WebSite', 'BreadcrumbList', 'Product'], $types);

        $product = $graph[3];
        $this->assertSame('Кешью 30 г', $product['name']);
        $this->assertSame('SAXIY', $product['brand']['name']);
        $this->assertArrayNotHasKey('offers', $product, 'B2B prices must not be published.');
        $this->assertSame($url, $graph[2]['itemListElement'][3]['item']);
    }

    public function test_product_urls_are_scoped_to_their_category(): void
    {
        $other = Category::factory()->create();

        $this->get(route('product', [$other, $this->product]))->assertNotFound();
    }

    public function test_hidden_products_and_categories_are_not_published(): void
    {
        $hiddenProduct = Product::factory()->for($this->category)->inactive()->create();
        $hiddenCategory = Category::factory()->inactive()->create();

        $this->get(route('product', [$this->category, $hiddenProduct]))->assertNotFound();
        $this->get(route('category', $hiddenCategory))->assertNotFound();
        $this->get(route('category', $this->category))->assertOk()->assertDontSee($hiddenProduct->translate('name'));
    }

    public function test_category_page_lists_its_products(): void
    {
        $this->get(route('localized.category', ['en', $this->category]))
            ->assertOk()
            ->assertSee('<title>Nuts wholesale — direct from SAXIY</title>', false)
            ->assertSee('Roasted kernels.')
            ->assertSee(route('localized.product', ['en', $this->category, $this->product]), false);
    }

    public function test_contacts_and_social_links_from_settings_are_shown(): void
    {
        app(SiteSettings::class)->save([
            'phones' => ['+998 71 200-00-00'],
            'email' => 'sales@saxiy.uz',
            'telegram_contact' => 'saxiy_sales',
            'social' => ['linkedin' => 'https://linkedin.com/company/saxiy', 'instagram' => 'https://instagram.com/saxiy.uz'],
        ]);

        $response = $this->get(route('contact'))->assertOk();

        $response->assertSee('+998 71 200-00-00')
            ->assertSee('sales@saxiy.uz')
            ->assertSee('https://instagram.com/saxiy.uz', false)
            ->assertSee('LinkedIn')
            ->assertSee('https://t.me/saxiy_sales?text=', false)
            ->assertSee('@saxiy_sales')
            ->assertDontSee('{{', false)
            ->assertDontSee('wa.me');
        $this->assertSame(
            ['https://instagram.com/saxiy.uz', 'https://linkedin.com/company/saxiy'],
            $this->jsonLd($response->getContent())[0]['sameAs'],
            'Social profiles are listed in the fixed network order.',
        );
    }

    public function test_sitemap_lists_every_page_in_every_language(): void
    {
        Product::factory()->for($this->category)->inactive()->create(['name' => ['uz' => 'X', 'ru' => 'X', 'en' => 'Hidden thing']]);

        $response = $this->get(route('sitemap'))->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $xml = simplexml_load_string($response->getContent());
        $locations = array_map('strval', $xml->xpath('//*[local-name()="loc"]'));

        $this->assertCount(15, $locations, '5 pages (home, catalog, contact, category, product) × 3 languages');
        $this->assertContains(route('localized.product', ['ru', 'nuts', 'cashew-30g']), $locations);
        $this->assertStringNotContainsString('hidden-thing', $response->getContent());
    }

    public function test_robots_allows_crawling_only_in_production(): void
    {
        $this->get(route('robots'))->assertOk()->assertSee('Disallow: /');

        $this->app['env'] = 'production';

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Allow: /')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_llms_txt_summarises_the_catalog_for_ai_search(): void
    {
        $this->get(route('llms'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('# SAXIY Premium Snacks')
            ->assertSee('[Cashew, 30 g]('.route('localized.product', ['en', 'nuts', 'cashew-30g']).')', false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLd(string $html): array
    {
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $data['@context']);

        return $data['@graph'];
    }
}
