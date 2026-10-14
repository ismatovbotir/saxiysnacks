<?php

namespace App\Jobs;

use App\Jobs\Concerns\SyncsAdminChatMessage;
use App\Models\TelegramClient;
use App\Services\Telegram\AdminChatMessages;
use App\Services\Telegram\TelegramBot;
use App\Support\TelegramSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Posts a registration to the admin chat, or refreshes the existing post after a status change.
 */
class SyncClientAdminMessage implements ShouldQueue
{
    use Queueable, SyncsAdminChatMessage;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [5, 30];

    public function __construct(public TelegramClient $client) {}

    public function handle(TelegramBot $bot, TelegramSettings $settings, AdminChatMessages $messages): void
    {
        $this->syncAdminMessage(
            $bot,
            $settings,
            $this->client,
            $messages->clientText($this->client),
            $messages->clientKeyboard($this->client),
        );
    }
}
