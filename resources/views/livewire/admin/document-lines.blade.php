{{-- Document lines, MoySklad-style: a positions table edited in place, products added through the search row below it. --}}
@php
    $hasPrices = $this->documentType->hasPrices();
    $locale = app()->getLocale();
@endphp

<div>
    <div class="overflow-x-auto rounded-md border border-black/10">
        <table class="admin-table min-w-[44rem]">
            <thead>
                <tr>
                    <th class="admin-th w-10">№</th>
                    <th class="admin-th">{{ __('Product') }}</th>
                    <th class="admin-th w-28">{{ __('Unit') }}</th>
                    <th class="admin-th w-28 text-right">{{ __('Quantity') }}</th>
                    <th class="admin-th w-24 text-right">{{ __('Pieces') }}</th>
                    @if ($hasPrices)
                        <th class="admin-th w-36 text-right">{{ __('Price per unit, so\'m') }}</th>
                        <th class="admin-th w-36 text-right">{{ __('Amount') }}</th>
                    @else
                        <th class="admin-th w-32 text-right">{{ __('In stock now') }}</th>
                    @endif
                    @unless ($readOnly)<th class="w-10"></th>@endunless
                </tr>
            </thead>
            <tbody>
                @forelse ($lines as $index => $line)
                    @php($product = $this->products[(int) $line['product_id']] ?? null)
                    <tr wire:key="line-{{ $index }}-{{ $line['product_id'] }}">
                        <td class="admin-td text-black/45 tabular-nums">{{ $index + 1 }}</td>
                        <td class="admin-td">
                            <input type="hidden" name="lines[{{ $index }}][product_id]" value="{{ $line['product_id'] }}">
                            <span class="font-medium">{{ $product?->translate('name', $locale) ?? '—' }}</span>
                            @if ($product)
                                <span class="block text-xs text-black/50">{{ $product->weight_grams }} {{ __('g') }}{{ $product->sku ? ' · '.$product->sku : '' }}{{ $product->box_quantity ? ' · '.__(':count pcs per package', ['count' => $product->box_quantity]) : '' }}</span>
                            @endif
                            @foreach (['product_id', 'unit', 'quantity', 'price'] as $field)
                                @if ($message = $this->error("lines.{$index}.{$field}"))<span class="block text-xs text-red-600">{{ $message }}</span>@endif
                            @endforeach
                        </td>
                        <td class="admin-td">
                            @if ($readOnly)
                                {{ \App\Enums\OrderUnit::from($line['unit'])->label() }}
                            @else
                                <select name="lines[{{ $index }}][unit]" wire:model.live="lines.{{ $index }}.unit" class="admin-input h-8 py-0">
                                    <option value="piece">{{ __('pcs') }}</option>
                                    @if ($product?->box_quantity)<option value="package">{{ __('pkg') }} ({{ $product->box_quantity }})</option>@endif
                                </select>
                            @endif
                        </td>
                        <td class="admin-td text-right">
                            @if ($readOnly)
                                <span @class(['tabular-nums', 'text-red-700' => (int) $line['quantity'] < 0])>{{ number_format((int) $line['quantity'], 0, '.', ' ') }}</span>
                            @else
                                <input type="number" name="lines[{{ $index }}][quantity]" wire:model.live.debounce.300ms="lines.{{ $index }}.quantity" required
                                       @if ($this->documentType !== \App\Enums\StockDocumentType::Correction) min="1" @endif class="admin-input h-8 py-0 text-right tabular-nums">
                            @endif
                        </td>
                        <td @class(['admin-td text-right font-mono tabular-nums', 'text-red-700' => ($this->pieces($line) ?? 0) < 0])>{{ $this->pieces($line) !== null ? number_format($this->pieces($line), 0, '.', ' ') : '—' }}</td>
                        @if ($hasPrices)
                            <td class="admin-td text-right">
                                @if ($readOnly)
                                    <span class="tabular-nums">{{ $line['price'] !== '' ? \App\Support\Money::format((int) $line['price']) : '—' }}</span>
                                @else
                                    <input type="text" inputmode="numeric" name="lines[{{ $index }}][price]" wire:model.live.debounce.300ms="lines.{{ $index }}.price" class="admin-input h-8 py-0 text-right tabular-nums">
                                @endif
                            </td>
                            <td class="admin-td text-right font-semibold whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($this->amount($line)) }}</td>
                        @else
                            <td class="admin-td text-right font-mono text-black/60 tabular-nums">{{ $product ? number_format((int) $product->stock_balance, 0, '.', ' ') : '—' }}</td>
                        @endif
                        @unless ($readOnly)
                            <td class="admin-td text-right">
                                <button type="button" wire:click="removeLine({{ $index }})" class="admin-icon-btn hover:text-red-700" title="{{ __('Remove line') }}" aria-label="{{ __('Remove line') }}"><x-admin.icon name="trash" class="size-4" /></button>
                            </td>
                        @endunless
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-td py-8 text-center text-black/50">{{ __('No items yet. Find a product below to add it.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        @unless ($readOnly)
            <div class="relative w-full sm:max-w-md">
                <x-admin.icon name="plus" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-black/40" />
                <input type="search" wire:model.live.debounce.200ms="search" placeholder="{{ __('Add item: name, SKU or barcode…') }}" aria-label="{{ __('Add item') }}"
                       wire:keydown.enter.prevent="addFirstMatch" class="admin-input pl-9">
                @if ($search !== '')
                    <ul class="absolute inset-x-0 top-full z-20 mt-1 max-h-72 overflow-y-auto rounded-md border border-black/10 bg-white py-1 shadow-lg">
                        @forelse ($this->matches as $match)
                            <li>
                                <button type="button" wire:click="addProduct({{ $match->id }})" class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-brand/[0.06]">
                                    <span><span class="font-medium">{{ $match->translate('name', $locale) }}</span> <span class="text-xs text-black/50">{{ $match->weight_grams }} {{ __('g') }}{{ $match->sku ? ' · '.$match->sku : '' }}</span></span>
                                    <span class="shrink-0 font-mono text-xs text-black/50">{{ number_format((int) $match->stock_balance, 0, '.', ' ') }} {{ __('pcs') }}</span>
                                </button>
                            </li>
                        @empty
                            <li class="px-3 py-2 text-sm text-black/50">{{ __('Nothing found.') }}</li>
                        @endforelse
                    </ul>
                @endif
            </div>
        @else
            <span></span>
        @endunless

        <dl class="ml-auto grid min-w-56 grid-cols-[1fr_auto] gap-x-6 gap-y-1 text-sm">
            <dt class="text-black/60">{{ __('Items') }}</dt><dd class="text-right tabular-nums">{{ count($lines) }}</dd>
            <dt class="text-black/60">{{ __('Pieces') }}</dt><dd class="text-right tabular-nums">{{ number_format($this->totalPieces, 0, '.', ' ') }}</dd>
            @if ($hasPrices)
                <dt class="border-t border-black/10 pt-1 font-semibold">{{ __('Total') }}</dt><dd class="border-t border-black/10 pt-1 text-right text-base font-semibold tabular-nums">{{ $this->total }}</dd>
            @endif
        </dl>
    </div>
</div>
