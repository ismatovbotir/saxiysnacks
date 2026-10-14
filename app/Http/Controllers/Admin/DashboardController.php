<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * "Today" and "this week" follow the business's clock in Tashkent; timestamps are stored in UTC.
     */
    private const string TIMEZONE = 'Asia/Tashkent';

    public function __invoke(): View
    {
        $today = now(self::TIMEZONE)->startOfDay()->utc();
        $weekStart = now(self::TIMEZONE)->startOfWeek()->utc();

        return view('admin.dashboard', [
            'newOrders' => Order::where('status', OrderStatus::New)->count(),
            'ordersToday' => Order::where('created_at', '>=', $today)->count(),
            'weekRevenue' => (int) Order::where('created_at', '>=', $weekStart)->where('status', '!=', OrderStatus::Cancelled)->sum('total'),
            'weekOrders' => Order::where('created_at', '>=', $weekStart)->where('status', '!=', OrderStatus::Cancelled)->count(),
            'customers' => TelegramClient::where('status', ClientStatus::Active)->count(),
            'resellers' => TelegramClient::where('status', ClientStatus::Active)->where('type', ClientType::Reseller)->count(),
            'unpricedProducts' => Product::where('is_active', true)->whereNull('consumer_price')->count(),
            'recentOrders' => Order::with('client')->latest('id')->limit(8)->get(),
            'recentClients' => TelegramClient::latest('id')->limit(8)->get(),
        ]);
    }
}
