<?php

namespace App\Actions;

use App\Services\Telegram\TelegramBot;
use App\Support\TelegramSettings;
use RuntimeException;

class ConnectTelegramWebhook
{
    public function __construct(private TelegramSettings $settings) {}

    public static function webhookUrl(): string
    {
        return route('telegram.webhook');
    }

    public static function canConnect(): bool
    {
        return str_starts_with(self::webhookUrl(), 'https://');
    }

    /**
     * Register the webhook, the menu button that opens the Mini App, and the /start command.
     *
     * @throws RuntimeException when the bot is not configured or the site is not served over HTTPS
     */
    public function handle(): void
    {
        if (! $this->settings->isConfigured() || blank($this->settings->webhookSecret())) {
            throw new RuntimeException('Connect the bot token first.');
        }

        if (! self::canConnect()) {
            throw new RuntimeException('Telegram requires HTTPS, but the webhook address is '.self::webhookUrl().'. Set APP_URL to your public https:// domain.');
        }

        $bot = new TelegramBot($this->settings->botToken());

        $bot->call('setWebhook', [
            'url' => self::webhookUrl(),
            'secret_token' => $this->settings->webhookSecret(),
            'allowed_updates' => ['message', 'callback_query'],
        ]);

        $bot->call('setChatMenuButton', [
            'menu_button' => ['type' => 'web_app', 'text' => 'Catalog', 'web_app' => ['url' => route('webapp')]],
        ]);

        $bot->call('setMyCommands', [
            'commands' => [['command' => 'start', 'description' => 'Open the SAXIY catalog']],
        ]);
    }
}
