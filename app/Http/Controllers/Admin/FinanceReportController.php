<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseCategory;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Period summary: sales (orders placed, not cancelled), money received, expenses and net cash flow.
 */
class FinanceReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $period = ReportPeriod::fromRequest($request);

        $orders = Order::query()
            ->whereBetween('created_at', [$period->from, $period->to])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->get(['id', 'source', 'price_type', 'total', 'status']);

        $payments = $period->constrain(Payment::query(), 'paid_on')->get(['amount', 'method']);
        $expenses = $period->constrain(Expense::query(), 'spent_on')->get(['amount', 'category']);

        $received = (int) $payments->sum('amount');
        $spent = (int) $expenses->sum('amount');

        return view('admin.finance.report', [
            'period' => $period,
            'sales' => [
                'count' => $orders->count(),
                'total' => (int) $orders->sum('total'),
                'bySource' => collect(OrderSource::cases())->mapWithKeys(fn (OrderSource $source): array => [
                    $source->label() => (int) $orders->where('source', $source)->sum('total'),
                ]),
                'shipped' => (int) $orders->whereIn('status', [OrderStatus::Shipped, OrderStatus::Completed])->sum('total'),
            ],
            'received' => $received,
            'receivedByMethod' => collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $method): array => [
                $method->label() => (int) $payments->where('method', $method)->sum('amount'),
            ]),
            'spent' => $spent,
            'spentByCategory' => collect(ExpenseCategory::cases())
                ->mapWithKeys(fn (ExpenseCategory $category): array => [$category->label() => (int) $expenses->where('category', $category)->sum('amount')])
                ->filter()
                ->sortDesc(),
            'net' => $received - $spent,
        ]);
    }
}
