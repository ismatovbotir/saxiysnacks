<?php

namespace App\Actions;

use App\Enums\ClientType;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncClientAdminMessage;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;

class ChangeClientType
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Switch a client between consumer and reseller pricing, then update the admin chat post and tell the client.
     */
    public function handle(TelegramClient $client, ClientType $type): TelegramClient
    {
        if ($client->type === $type) {
            return $client;
        }

        $client->update(['type' => $type]);

        SyncClientAdminMessage::dispatch($client);
        SendTelegramMessage::dispatch(
            $client->telegram_id,
            $this->messages->typeChanged($client),
            $this->messages->catalogKeyboard($client->language_code),
        );

        return $client;
    }
}
