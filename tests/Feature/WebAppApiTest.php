<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class WebAppApiTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    /**
     * @return array<string, string>
     */
    private function registration(array $overrides = []): array
    {
        return [
            'first_name' => 'Aziz',
            'last_name' => 'Karimov',
            'phone' => '+998 90 123 45 67',
            'latitude' => 41.311081,
            'longitude' => 69.240562,
            'address' => 'Chilonzor 9, house 12',
            ...$overrides,
        ];
    }

    public function test_requests_without_valid_init_data_are_rejected(): void
    {
        $this->getJson(route('webapp.catalog'))->assertUnauthorized();

        $this->getJson(route('webapp.catalog'), ['X-Telegram-Init-Data' => 'user=%7B%22id%22%3A1%7D&hash=forged'])
            ->assertUnauthorized();
    }

    public function test_unregistered_users_see_the_catalog_without_prices(): void
    {
        $product = Product::factory()->create();
        Product::factory()->inactive()->create();
        Product::factory()->for(Category::factory()->inactive())->create();

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonMissingPath('products.0.price')
            ->assertJsonMissingPath('products.0.orderable');
    }

    public function test_consumers_see_consumer_prices(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        Product::factory()->create(['consumer_price' => 150000, 'reseller_price' => 120000]);
        Product::factory()->withoutPrices()->create();

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertJsonPath('products.0.price', 150000)
            ->assertJsonPath('products.0.orderable', true)
            ->assertJsonMissingPath('products.0.consumer_price')
            ->assertJsonPath('products.1.price', null)
            ->assertJsonPath('products.1.orderable', false);
    }

    public function test_resellers_see_reseller_prices_and_fall_back_to_consumer_prices(): void
    {
        TelegramClient::factory()->reseller()->create(['telegram_id' => 1001]);
        Product::factory()->create(['consumer_price' => 150000, 'reseller_price' => 120000]);
        Product::factory()->create(['consumer_price' => 90000, 'reseller_price' => null]);

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertJsonPath('products.0.price', 120000)
            ->assertJsonPath('products.0.consumer_price', 150000)
            ->assertJsonPath('products.1.price', 90000)
            ->assertJsonPath('products.1.orderable', true);
    }

    public function test_blocked_clients_do_not_see_prices(): void
    {
        TelegramClient::factory()->blocked()->create(['telegram_id' => 1001]);
        Product::factory()->create();

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertJsonMissingPath('products.0.price');
    }

    public function test_profile_reports_guest_status_for_unknown_users(): void
    {
        $this->getJson(route('webapp.profile.show'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertExactJson(['status' => 'guest', 'type' => null, 'client' => null]);
    }

    public function test_registering_makes_the_user_an_active_consumer_immediately(): void
    {
        $this->postJson(route('webapp.profile.store'), $this->registration(['first_name' => ' Aziz ']), $this->webAppHeaders(1001, 'uz'))
            ->assertCreated()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('type', 'consumer')
            ->assertJsonPath('client.first_name', 'Aziz')
            ->assertJsonPath('client.company_name', null);

        $client = TelegramClient::sole();
        $this->assertSame(1001, $client->telegram_id);
        $this->assertSame('aziz_shop', $client->telegram_username);
        $this->assertSame('uz', $client->language_code);
        $this->assertSame(ClientType::Consumer, $client->type);
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertSame(777, $client->admin_message_id);
        $this->assertSame(41.311081, $client->latitude);
        $this->assertSame(69.240562, $client->longitude);
        $this->assertSame('Chilonzor 9, house 12', $client->address);

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === self::ADMIN_CHAT_ID
            && str_contains($data['text'], 'Aziz Karimov')
            && str_contains($data['text'], 'Chilonzor 9, house 12')
            && $data['reply_markup']['inline_keyboard'][0][0]['callback_data'] === "client:reseller:{$client->uuid}");
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], "ro'yxatdan o'tdingiz"));
    }

    public function test_company_details_are_optional_but_validated(): void
    {
        $this->postJson(route('webapp.profile.store'), $this->registration(['company_name' => 'Aziz Market LLC', 'vat_number' => 'not a vat!']), $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['vat_number' => 'The vat number field format is invalid.']);

        $this->postJson(route('webapp.profile.store'), $this->registration(['company_name' => 'Aziz Market LLC', 'vat_number' => '305123456']), $this->webAppHeaders(1001))
            ->assertCreated()
            ->assertJsonPath('client.vat_number', '305123456');
    }

    public function test_registration_requires_name_surname_phone_and_location(): void
    {
        $this->postJson(route('webapp.profile.store'), [], $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'phone', 'latitude' => 'Please share your location.', 'longitude'])
            ->assertJsonMissingValidationErrors(['company_name', 'vat_number']);

        $this->assertDatabaseEmpty('telegram_clients');
    }

    public function test_registration_rejects_impossible_coordinates(): void
    {
        $this->postJson(route('webapp.profile.store'), $this->registration(['latitude' => 120, 'longitude' => 'east']), $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_registration_rejects_an_invalid_phone_number(): void
    {
        $this->postJson(route('webapp.profile.store'), $this->registration(['phone' => 'call me']), $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone' => 'The phone field format is invalid.']);
    }

    public function test_customers_can_update_their_details_without_changing_their_price_type(): void
    {
        $client = TelegramClient::factory()->reseller()->create(['telegram_id' => 1001, 'admin_message_id' => 5]);

        $this->postJson(route('webapp.profile.store'), $this->registration(['company_name' => 'New Name LLC', 'type' => 'consumer']), $this->webAppHeaders(1001))
            ->assertCreated();

        $client->refresh();
        $this->assertSame('New Name LLC', $client->company_name);
        $this->assertSame(ClientType::Reseller, $client->type);
        $this->assertTelegramCalled('editMessageText', fn (array $data): bool => $data['message_id'] === 5
            && str_contains($data['text'], 'New Name LLC'));
    }

    public function test_blocked_clients_cannot_change_their_registration(): void
    {
        TelegramClient::factory()->blocked()->create(['telegram_id' => 1001, 'first_name' => 'Original']);

        $this->postJson(route('webapp.profile.store'), $this->registration(), $this->webAppHeaders(1001))->assertForbidden();

        $this->assertDatabaseHas('telegram_clients', ['telegram_id' => 1001, 'first_name' => 'Original', 'status' => 'blocked']);
    }

    public function test_unregistered_and_blocked_users_cannot_place_orders(): void
    {
        $product = Product::factory()->create();
        TelegramClient::factory()->blocked()->create(['telegram_id' => 1002]);

        foreach ([1001, 1002] as $telegramId) {
            $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]], $this->webAppHeaders($telegramId))
                ->assertForbidden();
        }

        $this->assertDatabaseEmpty('orders');
    }

    public function test_consumers_must_choose_a_delivery_location_for_every_order(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        $product = Product::factory()->create();

        $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]], $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude' => 'Please choose a delivery location.']);

        $this->assertDatabaseEmpty('orders');
    }

    public function test_consumers_are_charged_consumer_prices_and_their_location_is_remembered(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001, 'latitude' => 40.0, 'longitude' => 70.0, 'address' => 'Old address']);
        $kurt = Product::factory()->create(['consumer_price' => 120000, 'reseller_price' => 100000, 'weight_grams' => 30, 'name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt']]);
        $nuts = Product::factory()->create(['consumer_price' => 95000, 'reseller_price' => 80000]);

        $this->postJson(route('webapp.orders.store'), [
            'items' => [
                ['product_id' => $kurt->id, 'quantity' => 3, 'price' => 1],
                ['product_id' => $nuts->id, 'quantity' => 2],
            ],
            'comment' => 'Deliver on Monday',
            'latitude' => 41.2995,
            'longitude' => 69.2401,
            'address' => 'Office, 3rd floor',
        ], $this->webAppHeaders(1001))
            ->assertCreated()
            ->assertJsonPath('data.total', 550000)
            ->assertJsonPath('data.delivery.address', 'Office, 3rd floor')
            ->assertJsonPath('data.price_type', 'consumer')
            ->assertJsonPath('data.status', 'new');

        $order = Order::sole();
        $this->assertTrue($order->client->is($client));
        $this->assertSame(ClientType::Consumer, $order->price_type);
        $this->assertSame('Deliver on Monday', $order->comment);
        $this->assertSame([41.2995, 69.2401], [$order->delivery_latitude, $order->delivery_longitude]);
        $this->assertSame([41.2995, 69.2401, 'Office, 3rd floor'], [$client->refresh()->latitude, $client->longitude, $client->address]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $kurt->id,
            'product_name' => 'Курт',
            'weight_grams' => 30,
            'quantity' => 3,
            'unit_price' => 120000,
            'line_total' => 360000,
        ]);

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === self::ADMIN_CHAT_ID
            && str_contains($data['text'], $order->number())
            && $data['reply_markup']['inline_keyboard'][0][0]['callback_data'] === "order:confirmed:{$order->uuid}");
        $this->assertTelegramCalled('sendLocation', fn (array $data): bool => $data['chat_id'] === self::ADMIN_CHAT_ID
            && $data['latitude'] === 41.2995
            && $data['reply_parameters']['message_id'] === 777);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], $order->number()));
    }

    public function test_resellers_are_charged_reseller_prices_and_delivered_to_their_saved_location(): void
    {
        TelegramClient::factory()->reseller()->create(['telegram_id' => 1001, 'latitude' => 41.35, 'longitude' => 69.28, 'address' => 'Warehouse 4']);
        $kurt = Product::factory()->create(['consumer_price' => 120000, 'reseller_price' => 100000]);
        $nuts = Product::factory()->create(['consumer_price' => 95000, 'reseller_price' => null]);

        $this->postJson(route('webapp.orders.store'), ['items' => [
            ['product_id' => $kurt->id, 'quantity' => 3],
            ['product_id' => $nuts->id, 'quantity' => 2],
        ]], $this->webAppHeaders(1001))
            ->assertCreated()
            ->assertJsonPath('data.total', 490000)
            ->assertJsonPath('data.price_type', 'reseller')
            ->assertJsonPath('data.delivery.address', 'Warehouse 4');

        $this->assertSame([41.35, 69.28], [Order::sole()->delivery_latitude, Order::sole()->delivery_longitude]);

        $this->assertDatabaseHas('order_items', ['product_id' => $kurt->id, 'unit_price' => 100000, 'line_total' => 300000]);
        $this->assertDatabaseHas('order_items', ['product_id' => $nuts->id, 'unit_price' => 95000, 'line_total' => 190000]);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === self::ADMIN_CHAT_ID
            && str_contains($data['text'], 'Reseller'));
    }

    public function test_resellers_without_a_saved_location_are_asked_for_one(): void
    {
        TelegramClient::factory()->reseller()->create(['telegram_id' => 1001, 'latitude' => null, 'longitude' => null]);
        $product = Product::factory()->create();

        $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]], $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_a_failed_order_does_not_change_the_saved_location(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001, 'latitude' => 40.0, 'longitude' => 70.0]);
        $hidden = Product::factory()->inactive()->create();

        $this->postJson(route('webapp.orders.store'), [
            'items' => [['product_id' => $hidden->id, 'quantity' => 1]],
            'latitude' => 41.0,
            'longitude' => 69.0,
        ], $this->webAppHeaders(1001))->assertUnprocessable();

        $this->assertSame([40.0, 70.0], [$client->refresh()->latitude, $client->longitude]);
    }

    public function test_orders_with_unavailable_products_are_rejected(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        $available = Product::factory()->create();
        $hidden = Product::factory()->inactive()->create();
        $unpriced = Product::factory()->withoutPrices()->create();

        foreach ([$hidden, $unpriced] as $product) {
            $this->postJson(route('webapp.orders.store'), ['items' => [
                ['product_id' => $available->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 1],
            ], 'latitude' => 41.3, 'longitude' => 69.2], $this->webAppHeaders(1001))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['items' => 'Some products are no longer available. Please refresh the catalog.']);
        }

        $this->assertDatabaseEmpty('orders');
    }

    public function test_order_lines_must_have_a_positive_quantity(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        $product = Product::factory()->create();

        $this->postJson(route('webapp.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 0]]], $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_clients_only_see_their_own_orders(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001]);
        $own = Order::factory()->for($client, 'client')->create(['status' => OrderStatus::Confirmed]);
        Order::factory()->create();

        $this->getJson(route('webapp.orders.index'), $this->webAppHeaders(1001))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $own->uuid)
            ->assertJsonPath('data.0.status', 'confirmed');
    }
}
