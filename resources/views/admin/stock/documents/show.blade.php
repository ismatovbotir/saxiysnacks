@php
    $type = $document->type;
    $editable = ! $document->exists || $document->isDraft();
    $site = app(\App\Support\SiteSettings::class);
    $locale = app()->getLocale();
    $title = $document->exists
        ? __(':type № :number dated :date', ['type' => $type->label(), 'number' => $document->number, 'date' => $document->document_date->format('d.m.Y')])
        : __('New :type', ['type' => mb_strtolower($type->label())]);
    $savedLines = $document->exists ? $document->lines->map(fn ($line) => ['product_id' => $line->product_id, 'unit' => $line->unit->value, 'quantity' => $line->quantity, 'price' => $line->price])->all() : [];
    $lines = $editable ? old('lines', $savedLines) : $savedLines;
    $formId = 'stock-document-form';
@endphp

<x-admin.layout :title="$document->exists ? $document->number : $title">
    {{-- Toolbar --}}
    <div class="sticky top-14 z-10 -mx-4 mb-4 border-b border-black/10 bg-paper/95 px-4 py-2.5 lg:-mx-8 lg:px-8 print:hidden">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route($type->permission()->route()) }}" class="admin-icon-btn border-black/10 bg-white" title="{{ $type->pluralLabel() }}" aria-label="{{ $type->pluralLabel() }}"><x-admin.icon name="back" class="size-4" /></a>
            <h1 class="mr-auto min-w-0 truncate text-lg font-semibold tracking-tight">{{ $title }}</h1>
            @if ($document->exists)<x-admin.document-status :status="$document->status" />@endif

            @if ($editable)
                <button form="{{ $formId }}" class="admin-btn-outline">{{ __('Save') }}</button>
                <button form="{{ $formId }}" name="post" value="1" class="admin-btn bg-leaf hover:bg-leaf-dark"><x-admin.icon name="check" class="size-4" /> {{ __('Save and post') }}</button>
                @if ($document->exists)
                    <form method="POST" action="{{ route('admin.stock.documents.destroy', $document) }}" onsubmit="return confirm(@js(__('Delete this draft?')))">
                        @csrf @method('DELETE')
                        <button class="admin-icon-btn border-black/10 bg-white hover:text-red-700" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-admin.icon name="trash" class="size-4" /></button>
                    </form>
                @endif
            @else
                <button type="button" onclick="window.print()" class="admin-btn-outline"><x-admin.icon name="printer" class="size-4" /> {{ __('Print') }}</button>
                @if ($document->isPosted() && $document->order_id === null)
                    <form method="POST" action="{{ route('admin.stock.documents.unpost', $document) }}" onsubmit="return confirm(@js(__('Unpost this document? Its stock movements are removed and it becomes an editable draft.')))">
                        @csrf
                        <button class="admin-btn-outline"><x-admin.icon name="undo" class="size-4" /> {{ __('Unpost') }}</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    @error('document')<p class="mb-4 rounded-md border border-red-200 border-l-4 border-l-red-600 bg-white px-4 py-2.5 text-sm text-red-800 print:hidden">{{ $message }}</p>@enderror
    @if ($document->order_id)
        <p class="mb-4 rounded-md bg-black/[0.03] px-4 py-2.5 text-[13px] text-black/70 ring-1 ring-black/10 ring-inset print:hidden">{{ __('Created automatically when order :number was shipped. It follows the order: cancelling the order cancels this invoice.', ['number' => $document->order->number()]) }}</p>
    @endif

    <form id="{{ $formId }}" method="POST" action="{{ $document->exists ? route('admin.stock.documents.update', $document) : route('admin.stock.'.$type->slug().'.store') }}">
        @csrf
        @if ($document->exists) @method('PUT') @endif

        <article class="admin-card p-4 sm:p-6 print:border-0 print:p-0 print:shadow-none">
            {{-- Printed heading --}}
            <header class="mb-5 hidden items-start justify-between gap-4 border-b border-black/10 pb-4 print:flex">
                <div>
                    <p class="text-lg font-semibold">{{ $site->text('legal_name', $locale) }}</p>
                    <p class="text-xs text-black/60">{{ $site->text('address', $locale) }} · {{ $site->primaryPhone() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ $type->label() }}</p>
                    <p class="font-mono text-2xl font-semibold">№ {{ $document->number }}</p>
                    <p class="text-sm">{{ $document->document_date?->format('d.m.Y') }}</p>
                </div>
            </header>

            {{-- Header fields --}}
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 print:grid-cols-3">
                <div>
                    <span class="admin-label">{{ __('Number') }}</span>
                    <span class="block rounded-md bg-black/[0.04] px-3 py-1.5 font-mono text-sm">{{ $document->number ?? __('assigned on save') }}</span>
                </div>
                @if ($editable)
                    <x-admin.field :label="__('Date').' *'" name="document_date">
                        <input type="date" name="document_date" required max="{{ today()->toDateString() }}" value="{{ old('document_date', $document->document_date?->toDateString()) }}" class="admin-input">
                    </x-admin.field>
                    @if ($type === \App\Enums\StockDocumentType::Sale)
                        <div class="xl:col-span-2">
                            <livewire:admin.client-picker :label="$type->counterpartyLabel()" :value="old('counterparty', $document->counterparty)"
                                :client-id="old('telegram_client_id', $document->telegram_client_id) ? (int) old('telegram_client_id', $document->telegram_client_id) : null"
                                :error="$errors->first('counterparty') ?: $errors->first('telegram_client_id') ?: null" />
                        </div>
                    @else
                        <x-admin.field :label="$type->counterpartyLabel()" name="counterparty" class="xl:col-span-2">
                            <input type="text" name="counterparty" value="{{ old('counterparty', $document->counterparty) }}" class="admin-input">
                        </x-admin.field>
                    @endif
                @else
                    <div><span class="admin-label">{{ __('Date') }}</span><span class="block py-1.5 text-sm">{{ $document->document_date->format('d.m.Y') }}</span></div>
                    <div class="xl:col-span-2"><span class="admin-label">{{ $type->counterpartyLabel() }}</span><span class="block py-1.5 text-sm font-medium">@if ($document->client)<a href="{{ route('admin.clients.show', $document->client) }}" class="hover:underline print:no-underline">{{ $document->counterparty ?? $document->client->displayName() }}</a>@else{{ $document->counterparty ?? '—' }}@endif
                        @if ($document->order) · <a href="{{ route('admin.orders.show', $document->order) }}" class="hover:underline print:no-underline">{{ __('Order') }} {{ $document->order->number() }}</a>@endif</span></div>
                @endif
            </div>

            {{-- Positions --}}
            <div class="mt-6">
                <h2 class="mb-2 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Items') }}</h2>
                @error('lines')<p class="mb-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @if ($type === \App\Enums\StockDocumentType::Correction && $editable)
                    <p class="mb-2 text-xs text-black/55">{{ __('Plus adds stock (surplus), minus removes it (shortage, damage, write-off).') }}</p>
                @endif
                <livewire:admin.document-lines :type="$type" :lines="$lines" :field-errors="$errors->getMessages()" :read-only="! $editable" :key="'lines-'.($document->id ?? 'new')" />
            </div>

            {{-- Comment --}}
            <div class="mt-6">
                @if ($editable)
                    <x-admin.field :label="__('Comment')" name="note">
                        <textarea name="note" rows="2" class="admin-input">{{ old('note', $document->note) }}</textarea>
                    </x-admin.field>
                @elseif ($document->note)
                    <span class="admin-label">{{ __('Comment') }}</span><p class="text-sm">{{ $document->note }}</p>
                @endif
            </div>

            {{-- Printed signatures --}}
            <footer class="mt-10 hidden grid-cols-2 gap-10 text-sm print:grid">
                @foreach ([$type === \App\Enums\StockDocumentType::Receipt ? __('Delivered by') : __('Issued by'), __('Received by')] as $role)
                    <div><span class="block border-b border-black/40 pb-6"></span><span class="mt-1 block text-xs text-black/60">{{ $role }} · {{ __('signature') }}</span></div>
                @endforeach
            </footer>

            @if ($document->exists)
                <p class="mt-6 border-t border-black/[0.06] pt-3 text-[11px] text-black/45 print:hidden">
                    {{ __('Created by :name on :date.', ['name' => $document->creator?->name ?? '—', 'date' => $document->created_at->format('d.m.Y H:i')]) }}
                    @if ($document->posted_at) {{ __('Posted by :name on :date.', ['name' => $document->poster?->name ?? '—', 'date' => $document->posted_at->format('d.m.Y H:i')]) }} @endif
                </p>
            @endif
        </article>
    </form>
</x-admin.layout>
