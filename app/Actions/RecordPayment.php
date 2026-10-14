<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    /**
     * Record money received against an order. A payment cannot be larger than what is still owed.
     */
    public function handle(Order $order, int $amount, PaymentMethod $method, Carbon $paidOn, ?User $user = null, ?string $note = null): Payment
    {
        return DB::transaction(function () use ($order, $amount, $method, $paidOn, $user, $note): Payment {
            // Lock the order so two payments recorded at once cannot both fit under the amount due.
            $order = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $due = $order->loadSum('payments as paid', 'amount')->amountDue();

            if ($amount > $due) {
                throw ValidationException::withMessages(['amount' => __('Only :amount is still owed on this order.', ['amount' => Money::format($due)])]);
            }

            return $order->payments()->create([
                'telegram_client_id' => $order->telegram_client_id,
                'amount' => $amount,
                'method' => $method,
                'paid_on' => $paidOn,
                'user_id' => $user?->id,
                'note' => $note,
            ]);
        });
    }
}
