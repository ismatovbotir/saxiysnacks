<?php

namespace App\Actions;

use App\Enums\ClientStatus;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncClientAdminMessage;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;

class ChangeClientStatus
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Block or unblock a client, then update the admin chat post and tell the client.
     */
    public function handle(TelegramClient $client, ClientStatus $status): TelegramClient
    {
        if ($client->status === $status) {
            return $client;
        }

        $client->update(['status' => $status]);

        SyncClientAdminMessage::dispatch($client);
        SendTelegramMessage::dispatch($client->telegram_id, $this->messages->statusChanged($client));

        return $client;
    }
}
