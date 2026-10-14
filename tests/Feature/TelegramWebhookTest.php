<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    /**
     * @param  array<string, mixed>  $update
     */
    private function sendUpdate(array $update, string $secret = 'webhook-secret'): TestResponse
    {
        return $this->postJson(route('telegram.webhook'), $update, ['X-Telegram-Bot-Api-Secret-Token' => $secret]);
    }

    /**
     * @return array<string, mixed>
     */
    private function callbackUpdate(string $data, string $chatId = self::ADMIN_CHAT_ID): array
    {
        return ['callback_query' => [
            'id' => 'cbq-1',
            'from' => ['id' => 55],
            'data' => $data,
            'message' => ['message_id' => 777, 'chat' => ['id' => (int) $chatId, 'type' => 'supergroup']],
        ]];
    }

    public function test_updates_without_the_webhook_secret_are_rejected(): void
    {
        $this->sendUpdate(['message' => []], 'wrong')->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_start_shows_the_catalog_button_to_everyone(): void
    {
        $this->sendUpdate(['message' => [
            'text' => '/start',
            'chat' => ['id' => 1001, 'type' => 'private'],
            'from' => ['id' => 1001, 'language_code' => 'en'],
        ]])->assertNoContent();

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], 'Register in the app with your phone number')
            && $data['reply_markup']['inline_keyboard'][0][0]['web_app']['url'] === route('webapp'));
    }

    public function test_start_greets_resellers_in_their_language(): void
    {
        TelegramClient::factory()->reseller()->create(['telegram_id' => 1001, 'first_name' => 'Aziz', 'language_code' => 'ru']);

        $this->sendUpdate(['message' => [
            'text' => '/start',
            'chat' => ['id' => 1001, 'type' => 'private'],
            'from' => ['id' => 1001, 'language_code' => 'en'],
        ]])->assertNoContent();

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => str_contains($data['text'], 'С возвращением, <b>Aziz</b>! Ваши цены для реселлеров'));
    }

    public function test_a_location_shared_in_the_chat_becomes_the_saved_location(): void
    {
        $client = TelegramClient::factory()->reseller()->create(['telegram_id' => 1001, 'language_code' => 'ru', 'admin_message_id' => 777]);

        $this->sendUpdate(['message' => [
            'chat' => ['id' => 1001, 'type' => 'private'],
            'from' => ['id' => 1001, 'language_code' => 'ru'],
            'location' => ['latitude' => 41.35, 'longitude' => 69.28],
        ]])->assertNoContent();

        $client->refresh();
        $this->assertSame([41.35, 69.28], [$client->latitude, $client->longitude]);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], 'Локация сохранена'));
        $this->assertTelegramCalled('editMessageText', fn (array $data): bool => $data['message_id'] === 777);
    }

    public function test_locations_from_unregistered_users_are_not_stored(): void
    {
        $this->sendUpdate(['message' => [
            'chat' => ['id' => 1001, 'type' => 'private'],
            'from' => ['id' => 1001, 'language_code' => 'en'],
            'location' => ['latitude' => 41.35, 'longitude' => 69.28],
        ]])->assertNoContent();

        $this->assertDatabaseEmpty('telegram_clients');
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => str_contains($data['text'], 'Register in the app'));
    }

    public function test_admins_can_make_a_customer_a_reseller_from_the_admin_chat(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001, 'admin_message_id' => 777]);

        $this->sendUpdate($this->callbackUpdate("client:reseller:{$client->uuid}"))->assertNoContent();

        $this->assertSame(ClientType::Reseller, $client->refresh()->type);
        $this->assertTelegramCalled('editMessageText', fn (array $data): bool => $data['message_id'] === 777
            && str_contains($data['text'], 'Reseller prices')
            && $data['reply_markup']['inline_keyboard'][0][0]['callback_data'] === "client:consumer:{$client->uuid}");
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && isset($data['reply_markup']['inline_keyboard'][0][0]['web_app']));
        $this->assertTelegramCalled('answerCallbackQuery', fn (array $data): bool => $data['callback_query_id'] === 'cbq-1');
    }

    public function test_admins_can_block_and_unblock_a_customer(): void
    {
        $client = TelegramClient::factory()->create(['admin_message_id' => 777]);

        $this->sendUpdate($this->callbackUpdate("client:block:{$client->uuid}"))->assertNoContent();
        $this->assertSame(ClientStatus::Blocked, $client->refresh()->status);

        $this->sendUpdate($this->callbackUpdate("client:unblock:{$client->uuid}"))->assertNoContent();
        $this->assertSame(ClientStatus::Active, $client->refresh()->status);
    }

    public function test_callbacks_from_other_chats_are_ignored(): void
    {
        $client = TelegramClient::factory()->create();

        $this->sendUpdate($this->callbackUpdate("client:reseller:{$client->uuid}", '424242'))->assertNoContent();

        $this->assertSame(ClientType::Consumer, $client->refresh()->type);
        $this->assertTelegramCalled('answerCallbackQuery', fn (array $data): bool => $data['text'] === 'Not allowed.');
    }

    public function test_admins_can_move_an_order_to_the_next_status(): void
    {
        $order = Order::factory()->create(['admin_message_id' => 777]);

        $this->sendUpdate($this->callbackUpdate("order:confirmed:{$order->uuid}"))->assertNoContent();

        $this->assertSame(OrderStatus::Confirmed, $order->refresh()->status);
        $this->assertTelegramCalled('editMessageText', fn (array $data): bool => str_contains($data['text'], 'Confirmed')
            && $data['reply_markup']['inline_keyboard'][0][0]['callback_data'] === "order:shipped:{$order->uuid}");
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === $order->client->telegram_id
            && str_contains($data['text'], $order->number()));
    }

    public function test_stale_order_buttons_cannot_skip_or_revert_statuses(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Cancelled]);

        $this->sendUpdate($this->callbackUpdate("order:confirmed:{$order->uuid}"))->assertNoContent();

        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
        $this->assertTelegramCalled('answerCallbackQuery', fn (array $data): bool => $data['text'] === 'Order is already Cancelled.');
    }

    public function test_unknown_uuids_are_reported_back_to_the_admin(): void
    {
        $this->sendUpdate($this->callbackUpdate('client:reseller:00000000-0000-0000-0000-000000000000'))->assertNoContent();

        $this->assertTelegramCalled('answerCallbackQuery', fn (array $data): bool => $data['text'] === 'Customer not found.');
    }
}
