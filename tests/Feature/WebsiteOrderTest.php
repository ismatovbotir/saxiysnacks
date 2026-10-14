<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class WebsiteOrderTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    public function test_guests_order_pieces_with_a_name_and_phone(): void
    {
        $kurt = Product::factory()->create(['consumer_price' => 3000, 'box_quantity' => 40]);
        $nuts = Product::factory()->withoutPrices()->create();

        $this->postJson(route('order'), [
            'name' => '  Dilnoza ',
            'phone' => '+998 90 123 45 67',
            'comment' => 'After 18:00',
            'items' => [['slug' => $kurt->slug, 'quantity' => 12], ['slug' => $nuts->slug, 'quantity' => 2]],
        ])->assertCreated()->assertJsonPath('number', 'SX-00001');

        $order = Order::sole();
        $this->assertSame([OrderSource::Website, null, 'Dilnoza', '+998 90 123 45 67', 36000], [
            $order->source, $order->telegram_client_id, $order->customer_name, $order->customer_phone, $order->total,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $kurt->id, 'quantity' => 12, 'unit_price' => 3000, 'line_total' => 36000]);
        $this->assertDatabaseHas('order_items', ['product_id' => $nuts->id, 'quantity' => 2, 'unit_price' => 0]);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === self::ADMIN_CHAT_ID
            && str_contains($data['text'], 'Website order')
            && str_contains($data['text'], '12 pcs'));
    }

    public function test_the_phone_is_required_and_consumer_rules_apply(): void
    {
        $product = Product::factory()->create(['consumer_min_quantity' => 5, 'consumer_quantity_step' => 5]);

        $this->postJson(route('order'), ['name' => 'Ali', 'items' => [['slug' => $product->slug, 'quantity' => 5]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);

        $this->postJson(route('order'), ['name' => 'Ali', 'phone' => '+998901234567', 'items' => [['slug' => $product->slug, 'quantity' => 7]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);

        $this->postJson(route('order'), ['name' => 'Ali', 'phone' => '+998901234567', 'items' => [['slug' => 'missing-product', 'quantity' => 5]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);

        $this->assertDatabaseEmpty('orders');
    }

    public function test_admins_see_website_orders_and_can_change_their_status(): void
    {
        $order = Order::factory()->fromWebsite()->create(['customer_name' => 'Dilnoza', 'customer_phone' => '+998 90 123 45 67']);
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->get(route('admin.orders.index'))->assertOk()->assertSee('Dilnoza')->assertSee('Website');
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('call the customer to confirm');
        $this->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])->assertRedirect();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }
}
