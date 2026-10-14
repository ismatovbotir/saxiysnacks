<?php

namespace Tests\Feature;

use App\Enums\ClientType;
use App\Enums\OrderUnit;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class ProductOrderRulesTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    public function test_admins_set_price_minimum_and_step_per_customer_type(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->post(route('admin.products.store'), [
            'category_id' => Category::factory()->create()->id,
            'name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt'],
            'weight_grams' => 30,
            'sort_order' => 0,
            'is_active' => 1,
            'consumer_price' => '12 000',
            'consumer_min_quantity' => 1,
            'consumer_quantity_step' => 1,
            'reseller_price' => '10 000',
            'reseller_min_quantity' => 10,
            'reseller_quantity_step' => 5,
        ])->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertSame([12000, 1, 1], [$product->consumer_price, $product->consumer_min_quantity, $product->consumer_quantity_step]);
        $this->assertSame([10000, 10, 5], [$product->reseller_price, $product->reseller_min_quantity, $product->reseller_quantity_step]);

        $this->get(route('admin.products.index'))->assertSee('min 10 · step 5');
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('name="reseller_quantity_step"', false);
    }

    public function test_steps_must_be_positive(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->post(route('admin.products.store'), ['consumer_quantity_step' => 0, 'reseller_min_quantity' => -1])
            ->assertSessionHasErrors(['consumer_quantity_step', 'reseller_min_quantity']);
    }

    public function test_reseller_rules_are_counted_in_packages_and_default_to_one(): void
    {
        $product = Product::factory()->make(['consumer_min_quantity' => 3, 'consumer_quantity_step' => 2, 'reseller_min_quantity' => null, 'reseller_quantity_step' => 6]);

        $this->assertSame(1, $product->minQuantityFor(ClientType::Reseller));
        $this->assertSame(6, $product->quantityStepFor(ClientType::Reseller));
        $this->assertTrue($product->allowsQuantity(ClientType::Consumer, 5));
        $this->assertFalse($product->allowsQuantity(ClientType::Consumer, 4));
        $this->assertFalse($product->allowsQuantity(ClientType::Consumer, 1));
        $this->assertSame(7, $product->allowedQuantity(ClientType::Consumer, 6));
        $this->assertSame(3, $product->allowedQuantity(ClientType::Consumer, 1));
    }

    public function test_consumers_pay_per_piece_and_resellers_per_package(): void
    {
        $product = Product::factory()->make(['box_quantity' => 40, 'consumer_price' => 3000, 'reseller_price' => null]);

        $this->assertSame(OrderUnit::Piece, ClientType::Consumer->orderUnit());
        $this->assertSame(OrderUnit::Package, ClientType::Reseller->orderUnit());
        $this->assertSame(3000, $product->priceFor(ClientType::Consumer));
        $this->assertSame(120000, $product->priceFor(ClientType::Reseller));

        $product->box_quantity = null;
        $this->assertNull($product->priceFor(ClientType::Reseller));

        $product->reseller_price = 100000;
        $this->assertSame(100000, $product->priceFor(ClientType::Reseller));
    }

    public function test_the_catalog_sends_each_customer_their_own_rules(): void
    {
        $product = Product::factory()->create(['consumer_min_quantity' => 1, 'consumer_quantity_step' => 1, 'reseller_min_quantity' => 10, 'reseller_quantity_step' => 5]);
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        TelegramClient::factory()->reseller()->create(['telegram_id' => 2002]);

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertJsonPath('products.0.min_quantity', 1)
            ->assertJsonPath('products.0.quantity_step', 1);
        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(2002))
            ->assertJsonPath('products.0.min_quantity', 10)
            ->assertJsonPath('products.0.quantity_step', 5);
        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(3003))
            ->assertJsonMissingPath('products.0.min_quantity');

        $this->assertNotNull($product);
    }

    public function test_orders_below_the_minimum_or_off_step_are_rejected(): void
    {
        $product = Product::factory()->create(['reseller_price' => 10000, 'reseller_min_quantity' => 10, 'reseller_quantity_step' => 5]);
        TelegramClient::factory()->reseller()->create(['telegram_id' => 2002, 'latitude' => 41.3, 'longitude' => 69.2]);

        foreach ([5, 12] as $quantity) {
            $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => $quantity]]], $this->webAppHeaders(2002))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['items' => 'order at least 10 packages, in steps of 5.']);
        }

        $this->assertDatabaseEmpty('orders');

        $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 15]]], $this->webAppHeaders(2002))
            ->assertCreated()
            ->assertJsonPath('data.total', 150000);

        $this->assertSame(1, Order::count());
    }
}
