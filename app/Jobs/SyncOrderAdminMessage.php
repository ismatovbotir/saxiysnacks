<?php

namespace App\Jobs;

use App\Jobs\Concerns\SyncsAdminChatMessage;
use App\Models\Order;
use App\Services\Telegram\AdminChatMessages;
use App\Services\Telegram\TelegramBot;
use App\Support\TelegramSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Posts an order (with a delivery map pin) to the admin chat, or refreshes the existing post after a status change.
 */
class SyncOrderAdminMessage implements ShouldQueue
{
    use Queueable, SyncsAdminChatMessage;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [5, 30];

    public function __construct(public Order $order) {}

    public function handle(TelegramBot $bot, TelegramSettings $settings, AdminChatMessages $messages): void
    {
        $messageId = $this->syncAdminMessage(
            $bot,
            $settings,
            $this->order,
            $messages->orderText($this->order),
            $messages->orderKeyboard($this->order),
        );

        if ($messageId !== null) {
            $bot->sendLocation(
                $settings->adminChatId(),
                $this->order->delivery_latitude,
                $this->order->delivery_longitude,
                $messageId,
            );
        }
    }
}
