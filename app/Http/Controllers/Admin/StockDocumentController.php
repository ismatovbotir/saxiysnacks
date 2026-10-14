<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PostStockDocument;
use App\Actions\SaveStockDocument;
use App\Actions\UnpostStockDocument;
use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockDocumentRequest;
use App\Models\StockDocument;
use App\Support\ReportPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Warehouse documents, MoySklad-style: one list per type (receipts, corrections, sales invoices) and one screen per
 * document — editable while a draft, read-only once posted until it is unposted.
 */
class StockDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $type = StockDocumentType::from($request->route('documentType'));
        $status = StockDocumentStatus::tryFrom($request->string('status')->value());
        $period = $request->hasAny(['from', 'to']) ? ReportPeriod::fromRequest($request) : null;

        $query = StockDocument::query()
            ->where('type', $type)
            ->when($status, fn ($query, StockDocumentStatus $status) => $query->where('status', $status))
            ->when($period, fn ($query, ReportPeriod $period) => $period->constrain($query, 'document_date'))
            ->when($request->string('search')->trim()->value(), fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('number', 'like', "%{$search}%")->orWhere('counterparty', 'like', "%{$search}%")->orWhere('note', 'like', "%{$search}%"),
            ));

        return view('admin.stock.documents.index', [
            'type' => $type,
            'status' => $status,
            'period' => $period,
            'documents' => (clone $query)->with(['order', 'creator'])->withSum('lines as pieces_total', 'pieces')
                ->latest('document_date')->latest('id')->paginate(50)->withQueryString(),
            'totals' => [
                'amount' => (int) (clone $query)->where('status', StockDocumentStatus::Posted)->sum('total'),
                'count' => (clone $query)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $type = StockDocumentType::from($request->route('documentType'));

        return view('admin.stock.documents.show', [
            'document' => new StockDocument(['type' => $type, 'document_date' => today(), 'status' => StockDocumentStatus::Draft]),
        ]);
    }

    public function store(StockDocumentRequest $request, SaveStockDocument $save, PostStockDocument $post): RedirectResponse
    {
        $type = StockDocumentType::from($request->route('documentType'));
        $document = $save->handle($type, $request->validated(), $request->validated('lines'), $request->user());

        return $this->afterSave($request, $document, $post);
    }

    public function show(StockDocument $document): View
    {
        $this->authorizeType($document);

        return view('admin.stock.documents.show', ['document' => $document->load(['lines.product', 'order', 'client', 'creator', 'poster'])]);
    }

    public function update(StockDocumentRequest $request, StockDocument $document, SaveStockDocument $save, PostStockDocument $post): RedirectResponse
    {
        $this->authorizeType($document);

        $save->handle($document->type, $request->validated(), $request->validated('lines'), $request->user(), $document);

        return $this->afterSave($request, $document, $post);
    }

    public function post(Request $request, StockDocument $document, PostStockDocument $post): RedirectResponse
    {
        $this->authorizeType($document);

        $post->handle($document, $request->user());

        return back()->with('status', __('Document :number posted.', ['number' => $document->number]));
    }

    public function unpost(StockDocument $document, UnpostStockDocument $unpost): RedirectResponse
    {
        $this->authorizeType($document);

        $unpost->handle($document);

        return back()->with('status', __('Document :number unposted: it is a draft again.', ['number' => $document->number]));
    }

    public function destroy(StockDocument $document): RedirectResponse
    {
        $this->authorizeType($document);

        if (! $document->isDraft()) {
            return back()->with('error', __('Only drafts can be deleted; unpost the document first.'));
        }

        $document->delete();

        return redirect()->route($document->type->permission()->route())->with('status', __('Draft deleted.'));
    }

    private function afterSave(Request $request, StockDocument $document, PostStockDocument $post): RedirectResponse
    {
        if ($request->boolean('post')) {
            $post->handle($document, $request->user());
        }

        return redirect()->route('admin.stock.documents.show', $document)->with('status', $request->boolean('post')
            ? __('Document :number posted.', ['number' => $document->number])
            : __('Draft :number saved.', ['number' => $document->number]));
    }

    private function authorizeType(StockDocument $document): void
    {
        Gate::authorize('access-'.$document->type->permission()->value);
    }
}
