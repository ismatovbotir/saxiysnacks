<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class WebAppSavedListTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    public function test_customers_can_save_products_with_their_usual_quantity(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001]);
        [$kurt, $nuts] = Product::factory()->count(2)->create();

        $this->postJson(route('webapp.saved.store'), ['items' => [
            ['product_id' => $kurt->id, 'quantity' => 5],
            ['product_id' => $nuts->id, 'quantity' => 2],
        ]], $this->webAppHeaders(1001))
            ->assertOk()
            ->assertExactJson(['saved' => [$kurt->id => 5, $nuts->id => 2]]);

        $this->postJson(route('webapp.saved.store'), ['items' => [['product_id' => $kurt->id, 'quantity' => 8]]], $this->webAppHeaders(1001))
            ->assertExactJson(['saved' => [$kurt->id => 8, $nuts->id => 2]]);

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))
            ->assertJsonPath("saved.{$kurt->id}", 8)
            ->assertJsonPath("saved.{$nuts->id}", 2);

        $this->deleteJson(route('webapp.saved.destroy', $nuts), [], $this->webAppHeaders(1001))
            ->assertExactJson(['saved' => [$kurt->id => 8]]);

        $this->assertSame([$kurt->id => 8], $client->savedQuantities());
    }

    public function test_each_customer_only_sees_their_own_list(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        TelegramClient::factory()->create(['telegram_id' => 2002]);
        $product = Product::factory()->create();

        $this->postJson(route('webapp.saved.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]], $this->webAppHeaders(1001))->assertOk();

        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(2002))->assertJsonPath('saved', []);
    }

    public function test_guests_and_blocked_customers_cannot_save_products(): void
    {
        TelegramClient::factory()->blocked()->create(['telegram_id' => 2002]);
        $product = Product::factory()->create();
        $items = ['items' => [['product_id' => $product->id, 'quantity' => 1]]];

        $this->postJson(route('webapp.saved.store'), $items, $this->webAppHeaders(1001))->assertForbidden();
        $this->postJson(route('webapp.saved.store'), $items, $this->webAppHeaders(2002))->assertForbidden();
        $this->getJson(route('webapp.catalog'), $this->webAppHeaders(1001))->assertJsonPath('saved', []);

        $this->assertDatabaseEmpty('saved_products');
    }

    public function test_hidden_products_and_invalid_quantities_are_rejected(): void
    {
        TelegramClient::factory()->create(['telegram_id' => 1001]);
        $hidden = Product::factory()->inactive()->create();
        $product = Product::factory()->create();

        $this->postJson(route('webapp.saved.store'), ['items' => [
            ['product_id' => $hidden->id, 'quantity' => 1],
            ['product_id' => $product->id, 'quantity' => 0],
        ]], $this->webAppHeaders(1001))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id', 'items.1.quantity']);

        $this->assertDatabaseEmpty('saved_products');
    }
}
