<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\TelegramClient;
use App\Models\User;
use App\Support\TelegramSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class TelegramAdminTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    private const string NEW_TOKEN = '987654321:AAHnewTokenFromBotFather_1234567890';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['locale' => 'en']));
    }

    private function fakeTelegram(): void
    {
        Http::fake([
            'api.telegram.org/*/getMe' => Http::response(['ok' => true, 'result' => ['id' => 987654321, 'is_bot' => true, 'username' => 'saxiy_bot']]),
            'api.telegram.org/*/getWebhookInfo' => Http::response(['ok' => true, 'result' => ['url' => '', 'pending_update_count' => 0]]),
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);
    }

    private function useHttps(): void
    {
        URL::forceRootUrl('https://saxiy.uz');
        URL::forceScheme('https');
    }

    public function test_the_telegram_page_renders_before_and_after_connecting(): void
    {
        $this->fakeTelegram();

        $this->get(route('admin.telegram.edit'))->assertOk()->assertSee('Not connected')->assertSee('/link ');

        $this->configureTelegram();

        $this->get(route('admin.telegram.edit'))->assertOk()->assertSee(self::ADMIN_CHAT_ID);
    }

    public function test_connecting_a_bot_verifies_the_token_stores_it_encrypted_and_registers_the_webhook(): void
    {
        $this->useHttps();
        $this->fakeTelegram();

        $this->put(route('admin.telegram.bot'), ['bot_token' => self::NEW_TOKEN])
            ->assertSessionHas('status', 'Bot @saxiy_bot connected.');

        $settings = app(TelegramSettings::class);
        $this->assertSame(self::NEW_TOKEN, $settings->botToken());
        $this->assertSame('saxiy_bot', $settings->botUsername());
        $this->assertSame(48, strlen((string) $settings->webhookSecret()));
        $this->assertStringNotContainsString(self::NEW_TOKEN, json_encode(Setting::firstWhere('key', 'telegram')->value));

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.telegram.org/bot'.self::NEW_TOKEN.'/getMe');
        $this->assertTelegramCalled('setWebhook', fn (array $data): bool => $data['url'] === 'https://saxiy.uz/api/telegram/webhook'
            && $data['secret_token'] === $settings->webhookSecret());
        $this->assertTelegramCalled('setChatMenuButton', fn (array $data): bool => $data['menu_button']['web_app']['url'] === 'https://saxiy.uz/webapp');
    }

    public function test_the_webhook_accepts_the_secret_saved_in_the_admin_panel(): void
    {
        $this->fakeTelegram();
        $this->put(route('admin.telegram.bot'), ['bot_token' => self::NEW_TOKEN]);

        $secret = app(TelegramSettings::class)->webhookSecret();

        $this->postJson(route('telegram.webhook'), ['message' => []], ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'])->assertForbidden();
        $this->postJson(route('telegram.webhook'), ['message' => []], ['X-Telegram-Bot-Api-Secret-Token' => $secret])->assertNoContent();
    }

    public function test_a_token_telegram_rejects_is_not_saved(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 401, 'description' => 'Unauthorized'], 401)]);

        $this->put(route('admin.telegram.bot'), ['bot_token' => self::NEW_TOKEN])
            ->assertSessionHasErrors(['bot_token' => 'Telegram did not accept this token: Unauthorized']);
        $this->put(route('admin.telegram.bot'), ['bot_token' => 'not-a-token'])->assertSessionHasErrors('bot_token');

        $this->assertDatabaseMissing('settings', ['key' => 'telegram']);
    }

    public function test_without_https_the_token_is_saved_but_the_webhook_is_not_registered(): void
    {
        $this->fakeTelegram();

        $this->put(route('admin.telegram.bot'), ['bot_token' => self::NEW_TOKEN])
            ->assertSessionHas('status', 'Bot @saxiy_bot saved. Connect the webhook once the site runs on HTTPS.');

        $this->post(route('admin.telegram.webhook'))->assertSessionHas('error');

        Http::assertNotSent(fn ($request): bool => str_ends_with($request->url(), '/setWebhook'));
    }

    public function test_sending_the_link_code_in_a_group_makes_it_the_admin_chat(): void
    {
        $this->configureTelegram();
        $client = TelegramClient::factory()->create(['admin_message_id' => 10]);
        $order = Order::factory()->create(['admin_message_id' => 11]);
        $code = app(TelegramSettings::class)->adminChatLinkCode();

        $link = fn (string $text) => $this->postJson(route('telegram.webhook'), ['message' => [
            'message_id' => 1,
            'from' => ['id' => 55],
            'chat' => ['id' => -1009998887776, 'type' => 'supergroup'],
            'text' => $text,
        ]], ['X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret']);

        $link('/link WRONGCODE')->assertNoContent();
        $this->assertSame(self::ADMIN_CHAT_ID, app(TelegramSettings::class)->adminChatId());

        $link("/link@saxiy_bot {$code}")->assertNoContent();

        $this->assertSame('-1009998887776', app(TelegramSettings::class)->adminChatId());
        $this->assertNull($client->fresh()->admin_message_id);
        $this->assertNull($order->fresh()->admin_message_id);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === -1009998887776 && str_contains($data['text'], '✅'));

        $link("/link {$code}");
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => str_contains($data['text'], 'not valid any more'));
    }

    public function test_the_admin_chat_id_can_be_entered_by_hand_and_tested(): void
    {
        $this->configureTelegram();

        $this->put(route('admin.telegram.admin-chat'), ['admin_chat_id' => 'my group'])->assertSessionHasErrors('admin_chat_id');
        $this->put(route('admin.telegram.admin-chat'), ['admin_chat_id' => '-1001234567890'])->assertSessionHas('status');

        $this->post(route('admin.telegram.test'))->assertSessionHas('status', 'Test message sent to the admin chat.');

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === '-1001234567890');
    }

    public function test_the_telegram_page_warns_when_queued_messages_are_not_being_sent(): void
    {
        $this->fakeTelegram();
        config(['queue.default' => 'database']);

        $this->get(route('admin.telegram.edit'))->assertOk()->assertSee('No backlog')->assertDontSee('Worker not running');

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'available_at' => now()->subMinutes(10)->getTimestamp(),
            'created_at' => now()->subMinutes(10)->getTimestamp(),
        ]);

        $this->get(route('admin.telegram.edit'))->assertOk()->assertSee('Worker not running')->assertSee('php artisan queue:work');
    }
}
