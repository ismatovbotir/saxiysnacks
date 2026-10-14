<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncOrderAdminMessage;
use App\Models\Order;
use App\Services\Telegram\ClientMessages;

class ChangeOrderStatus
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Move an order to a new status, then update the admin chat post and tell the client.
     */
    public function handle(Order $order, OrderStatus $status): Order
    {
        if ($order->status === $status) {
            return $order;
        }

        $order->update(['status' => $status]);

        SyncOrderAdminMessage::dispatch($order);
        SendTelegramMessage::dispatch(
            $order->client->telegram_id,
            $this->messages->orderStatusChanged($order, $order->client),
        );

        return $order;
    }
}
