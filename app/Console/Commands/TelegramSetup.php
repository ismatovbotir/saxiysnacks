<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramBot;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('telegram:setup')]
#[Description('Register the bot webhook, the menu button that opens the Mini App, and the /start command')]
class TelegramSetup extends Command
{
    public function handle(TelegramBot $bot): int
    {
        if (! $bot->isConfigured() || blank(config('services.telegram.webhook_secret'))) {
            $this->error('Set TELEGRAM_BOT_TOKEN and TELEGRAM_WEBHOOK_SECRET in .env first.');

            return self::FAILURE;
        }

        $webhookUrl = route('telegram.webhook');
        $webAppUrl = route('webapp');

        if (! str_starts_with($webhookUrl, 'https://')) {
            $this->error("Telegram requires HTTPS. APP_URL resolves to {$webhookUrl} — use your public HTTPS domain (or an ngrok tunnel in development).");

            return self::FAILURE;
        }

        $bot->call('setWebhook', [
            'url' => $webhookUrl,
            'secret_token' => config('services.telegram.webhook_secret'),
            'allowed_updates' => ['message', 'callback_query'],
        ]);

        $bot->call('setChatMenuButton', [
            'menu_button' => ['type' => 'web_app', 'text' => 'Catalog', 'web_app' => ['url' => $webAppUrl]],
        ]);

        $bot->call('setMyCommands', [
            'commands' => [['command' => 'start', 'description' => 'Open the SAXIY catalog']],
        ]);

        $this->info("Webhook: {$webhookUrl}");
        $this->info("Mini App: {$webAppUrl}");

        if (blank(config('services.telegram.admin_chat_id'))) {
            $this->warn('TELEGRAM_ADMIN_CHAT_ID is empty — registrations and orders will not be posted to Telegram.');
        }

        return self::SUCCESS;
    }
}
