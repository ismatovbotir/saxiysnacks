<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProductUnits;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductUnitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_product_form_shows_a_piece_and_a_package_row(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));
        $product = Product::factory()->create(['package_code' => '1500423', 'box_package_code' => '1500999', 'box_quantity' => 40]);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSeeLivewire(ProductUnits::class)
            ->assertSee('name="package_code"', false)
            ->assertSee('name="box_package_code"', false)
            ->assertSee('name="box_quantity"', false)
            ->assertSee('1500999');
    }

    public function test_admins_save_package_codes_multiplier_and_prices_per_unit(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->post(route('admin.products.store'), [
            'category_id' => Category::factory()->create()->id,
            'name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt'],
            'weight_grams' => 30,
            'sort_order' => 0,
            'is_active' => 1,
            'package_code' => '1500423',
            'consumer_price' => '3 000',
            'box_package_code' => '15-00-999',
            'box_quantity' => 40,
            'reseller_price' => '100 000',
        ])->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertSame(['1500423', '1500999', 40, 3000, 100000], [
            $product->package_code, $product->box_package_code, $product->box_quantity, $product->consumer_price, $product->reseller_price,
        ]);
    }

    public function test_the_package_row_suggests_the_piece_price_times_the_multiplier(): void
    {
        $product = Product::factory()->make(['consumer_price' => 3000, 'box_quantity' => 40, 'reseller_price' => null, 'reseller_min_quantity' => 2, 'reseller_quantity_step' => 2]);

        Livewire::test(ProductUnits::class, ['product' => $product])
            ->assertSee("120 000 so'm")
            ->assertSee('80, 160, 240 …')
            ->set('packageMultiplier', '30')
            ->assertSee("90 000 so'm")
            ->assertSee('60, 120, 180 …');
    }

    public function test_the_reseller_price_is_entered_per_piece_and_saved_per_package(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));
        $form = [
            'category_id' => Category::factory()->create()->id,
            'name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt'],
            'weight_grams' => 30,
            'sort_order' => 0,
            'consumer_price' => '3 000',
            'reseller_piece_price' => '2 500',
        ];

        $this->post(route('admin.products.store'), $form)->assertSessionHasErrors('box_quantity');

        $this->post(route('admin.products.store'), [...$form, 'box_quantity' => 40])->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertSame([3000, 100000], [$product->consumer_price, $product->reseller_price]);

        $this->put(route('admin.products.update', $product), [...$form, 'box_quantity' => 40, 'reseller_piece_price' => ''])->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->reseller_price);
    }

    public function test_the_package_row_shows_the_package_price_for_the_piece_price(): void
    {
        $product = Product::factory()->make(['consumer_price' => 3000, 'box_quantity' => 40, 'reseller_price' => 100000]);

        Livewire::test(ProductUnits::class, ['product' => $product])
            ->assertSet('packagePiecePrice', '2500')
            ->assertSee("100 000 so'm")
            ->set('packagePiecePrice', '2 400')
            ->assertSee("96 000 so'm")
            ->set('packageMultiplier', '30')
            ->assertSee("72 000 so'm")
            ->set('packagePiecePrice', '')
            ->assertSee("90 000 so'm")
            ->assertSee('at the consumer piece price');
    }
}
