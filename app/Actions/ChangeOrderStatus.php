<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncOrderAdminMessage;
use App\Models\Order;
use App\Services\Telegram\ClientMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatus
{
    public function __construct(private ClientMessages $messages, private SyncOrderStock $syncStock) {}

    /**
     * Move an order to its next status (or any status with the owner's override) and keep the warehouse in step (shipping takes the pieces out of stock),
     * then update the admin chat post and tell the client (website guests are called instead).
     */
    public function handle(Order $order, OrderStatus $status, bool $override = false): Order
    {
        if ($order->status === $status) {
            return $order;
        }

        if (! $override && ! $order->status->canBecome($status)) {
            throw ValidationException::withMessages(['status' => __('An order cannot go from :from to :to.', ['from' => $order->status->label(), 'to' => $status->label()])]);
        }

        DB::transaction(function () use ($order, $status): void {
            $order->update(['status' => $status]);
            $this->syncStock->handle($order);
        });

        SyncOrderAdminMessage::dispatch($order);

        if ($order->client !== null) {
            SendTelegramMessage::dispatch(
                $order->client->telegram_id,
                $this->messages->orderStatusChanged($order, $order->client),
            );
        }

        return $order;
    }
}
