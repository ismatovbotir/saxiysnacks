<?php

namespace App\Console\Commands;

use App\Actions\ConnectTelegramWebhook;
use App\Support\TelegramSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('telegram:setup')]
#[Description('Register the bot webhook, the menu button that opens the Mini App, and the /start command (same as "Connect webhook" in Admin → Telegram)')]
class TelegramSetup extends Command
{
    public function handle(ConnectTelegramWebhook $connect, TelegramSettings $settings): int
    {
        try {
            $connect->handle();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Webhook: '.ConnectTelegramWebhook::webhookUrl());
        $this->info('Mini App: '.route('webapp'));

        if ($settings->adminChatId() === null) {
            $this->warn('No admin chat is linked — registrations and orders will not be posted to Telegram. Link one in Admin → Telegram.');
        }

        return self::SUCCESS;
    }
}
