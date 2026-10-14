<?php

namespace App\Jobs;

use App\Jobs\Concerns\SyncsAdminChatMessage;
use App\Models\Order;
use App\Services\Telegram\AdminChatMessages;
use App\Services\Telegram\TelegramBot;
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

    public function handle(TelegramBot $bot, AdminChatMessages $messages): void
    {
        $messageId = $this->syncAdminMessage(
            $bot,
            $this->order,
            $messages->orderText($this->order),
            $messages->orderKeyboard($this->order),
        );

        if ($messageId !== null) {
            $bot->sendLocation(
                config('services.telegram.admin_chat_id'),
                $this->order->delivery_latitude,
                $this->order->delivery_longitude,
                $messageId,
            );
        }
    }
}
