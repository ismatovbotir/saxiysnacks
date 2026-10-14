<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordPayment;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Support\ReportPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $period = ReportPeriod::fromRequest($request);
        $method = PaymentMethod::tryFrom($request->string('method')->value());

        $query = $period->constrain(Payment::query(), 'paid_on')

            ->when($method, fn ($query, PaymentMethod $method) => $query->where('method', $method));

        return view('admin.finance.payments', [
            'payments' => (clone $query)->with(['order.client', 'client', 'user'])->latest('paid_on')->latest('id')->paginate(50)->withQueryString(),
            'total' => (int) (clone $query)->sum('amount'),
            'period' => $period,
            'method' => $method,
        ]);
    }

    public function store(PaymentRequest $request, Order $order, RecordPayment $recordPayment): RedirectResponse
    {
        $recordPayment->handle(
            $order,
            $request->integer('amount'),
            $request->enum('method', PaymentMethod::class),
            Carbon::parse($request->validated('paid_on')),
            $request->user(),
            $request->validated('note'),
        );

        return back()->with('status', __('Payment recorded for order :number.', ['number' => $order->number()]));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return back()->with('status', __('Payment deleted.'));
    }
}
