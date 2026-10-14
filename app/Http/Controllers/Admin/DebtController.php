<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TelegramClient;
use Illuminate\View\View;

/**
 * Receivables: what customers owe for shipped orders, per customer and per unpaid order.
 */
class DebtController extends Controller
{
    /**
     * @var array<int, OrderStatus>
     */
    private const array BILLED = [OrderStatus::Shipped, OrderStatus::Completed];

    public function __invoke(): View
    {
        $customers = TelegramClient::query()
            ->withSum(['orders as billed_total' => fn ($query) => $query->whereIn('status', self::BILLED)], 'total')
            ->withSum('payments as paid_total', 'amount')
            ->get()
            ->each(fn (TelegramClient $client) => $client->setAttribute('balance', (int) $client->billed_total - (int) $client->paid_total))
            ->filter(fn (TelegramClient $client): bool => $client->balance !== 0)
            ->sortByDesc('balance')
            ->values();

        $unpaidOrders = Order::query()
            ->with('client')
            ->whereIn('status', self::BILLED)
            ->withSum('payments as paid', 'amount')
            ->oldest('id')
            ->get()
            ->filter(fn (Order $order): bool => $order->amountDue() > 0)
            ->values();

        return view('admin.finance.debts', [
            'customers' => $customers,
            'unpaidOrders' => $unpaidOrders,
            'totalDue' => $unpaidOrders->sum(fn (Order $order): int => $order->amountDue()),
        ]);
    }
}
