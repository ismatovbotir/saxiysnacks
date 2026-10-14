<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCodesTest extends TestCase
{
    use RefreshDatabase;

    private const string VALID_GTIN = '4006381333931';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['locale' => 'en']));
    }

    /**
     * @return array<string, mixed>
     */
    private function productForm(array $overrides = []): array
    {
        return [
            'category_id' => Category::factory()->create()->id,
            'name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt'],
            'weight_grams' => 30,
            'sort_order' => 0,
            'is_active' => 1,
            ...$overrides,
        ];
    }

    public function test_codes_are_saved_with_spaces_and_dashes_removed(): void
    {
        $this->post(route('admin.products.store'), $this->productForm([
            'gtin' => '4 006381 333931',
            'class_code' => '02106-001-001000000',
            'package_code' => ' 1500423 ',
        ]))->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertSame([self::VALID_GTIN, '02106001001000000', '1500423'], [$product->gtin, $product->class_code, $product->package_code]);
    }

    public function test_codes_are_optional(): void
    {
        $this->post(route('admin.products.store'), $this->productForm(['gtin' => '', 'class_code' => '', 'package_code' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Product::sole()->gtin);
    }

    public function test_invalid_codes_are_rejected(): void
    {
        $this->post(route('admin.products.store'), $this->productForm([
            'gtin' => '4006381333932',
            'class_code' => '12345',
            'package_code' => 'box',
        ]))->assertSessionHasErrors([
            'gtin' => 'The GTIN check digit is wrong — please check the barcode number.',
            'class_code' => 'The class code (MXIK / ИКПУ) must be exactly 17 digits.',
            'package_code' => 'The package code must contain digits only.',
        ]);

        $this->post(route('admin.products.store'), $this->productForm(['gtin' => '12345']))
            ->assertSessionHasErrors(['gtin' => 'The GTIN must have 8, 12, 13 or 14 digits.']);

        $this->assertDatabaseEmpty('products');
    }

    public function test_two_products_cannot_share_a_gtin_but_a_product_keeps_its_own(): void
    {
        $existing = Product::factory()->create(['gtin' => self::VALID_GTIN]);

        $this->post(route('admin.products.store'), $this->productForm(['gtin' => self::VALID_GTIN]))
            ->assertSessionHasErrors(['gtin' => 'Another product already uses this GTIN.']);

        $this->put(route('admin.products.update', $existing), $this->productForm(['gtin' => self::VALID_GTIN, 'category_id' => $existing->category_id]))
            ->assertSessionHasNoErrors();
    }

    public function test_the_gtin_is_published_in_the_product_structured_data(): void
    {
        $product = Product::factory()->create(['gtin' => self::VALID_GTIN]);

        $this->get(route('product', [$product->category, $product]))
            ->assertOk()
            ->assertSee('"gtin":"'.self::VALID_GTIN.'"', false);
    }
}
