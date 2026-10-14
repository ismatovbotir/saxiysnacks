<?php

namespace App\Actions;

use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncClientAdminMessage;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;
use App\Support\BotLocale;
use Illuminate\Support\Arr;

class RegisterTelegramClient
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Register a new customer (consumer prices) or update an existing customer's details.
     * Registration does not ask for a location — it is asked with the first order; a location sent
     * from "Edit profile" replaces the saved one. Type and status stay under admin control.
     *
     * @param  array{id: int, username?: string, language_code?: string}  $telegramUser
     * @param  array{first_name: string, last_name: string, phone: string, company_name?: string|null, vat_number?: string|null, latitude?: float|null, longitude?: float|null, address?: string|null}  $details
     */
    public function handle(array $telegramUser, array $details): TelegramClient
    {
        $client = TelegramClient::firstOrNew(['telegram_id' => $telegramUser['id']]);
        $isNew = ! $client->exists;

        $location = Arr::only($details, ['latitude', 'longitude', 'address']);

        $client->fill([
            'company_name' => null,
            'vat_number' => null,
            ...Arr::except($details, ['latitude', 'longitude', 'address']),
            'telegram_username' => $telegramUser['username'] ?? null,
            'language_code' => BotLocale::fromTelegram($telegramUser['language_code'] ?? null),
        ]);

        if (filled($location['latitude'] ?? null)) {
            $client->moveTo((float) $location['latitude'], (float) $location['longitude'], $location['address'] ?? null);
        } else {
            $client->save();
        }

        SyncClientAdminMessage::dispatch($client);

        if ($isNew) {
            SendTelegramMessage::dispatch($client->telegram_id, $this->messages->registered($client));
        }

        return $client;
    }
}
