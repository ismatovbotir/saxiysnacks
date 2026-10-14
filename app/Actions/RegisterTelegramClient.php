<?php

namespace App\Actions;

use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncClientAdminMessage;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;
use App\Support\BotLocale;

class RegisterTelegramClient
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Register a new customer (consumer prices) or update an existing customer's details.
     * Type and status stay under admin control.
     *
     * @param  array{id: int, username?: string, language_code?: string}  $telegramUser
     * @param  array{first_name: string, last_name: string, phone: string, company_name?: string|null, vat_number?: string|null, latitude: float, longitude: float, address?: string|null}  $details
     */
    public function handle(array $telegramUser, array $details): TelegramClient
    {
        $client = TelegramClient::firstOrNew(['telegram_id' => $telegramUser['id']]);
        $isNew = ! $client->exists;

        $client->fill([
            'company_name' => null,
            'vat_number' => null,
            ...$details,
            'location_updated_at' => now(),
            'telegram_username' => $telegramUser['username'] ?? null,
            'language_code' => BotLocale::fromTelegram($telegramUser['language_code'] ?? null),
        ])->save();

        SyncClientAdminMessage::dispatch($client);

        if ($isNew) {
            SendTelegramMessage::dispatch($client->telegram_id, $this->messages->registered($client));
        }

        return $client;
    }
}
