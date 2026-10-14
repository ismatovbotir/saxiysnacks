<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Support\ReportPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $period = ReportPeriod::fromRequest($request);
        $category = ExpenseCategory::tryFrom($request->string('category')->value());

        $query = $period->constrain(Expense::query(), 'spent_on')

            ->when($category, fn ($query, ExpenseCategory $category) => $query->where('category', $category));

        return view('admin.finance.expenses', [
            'expenses' => (clone $query)->with('user')->latest('spent_on')->latest('id')->paginate(50)->withQueryString(),
            'total' => (int) (clone $query)->sum('amount'),
            'period' => $period,
            'category' => $category,
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        Expense::create([...$request->validated(), 'user_id' => $request->user()->id]);

        return back()->with('status', __('Expense added.'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('status', __('Expense deleted.'));
    }
}
