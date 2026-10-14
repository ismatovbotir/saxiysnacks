{{-- One row per order unit; phones get one card per unit, wider screens a table. Inputs keep their form names. --}}
@php
    $rows = [
        [
            'title' => __('Piece'), 'unit' => __('pcs'), 'who' => '👤 '.__('Consumers'),
            'code' => ['package_code', 'pieceCode'], 'multiplier' => null, 'price' => ['consumer_price', 'piecePrice'],
            'min' => ['consumer_min_quantity', 'pieceMin'], 'step' => ['consumer_quantity_step', 'pieceStep'], 'base' => true,
        ],
        [
            'title' => __('Package'), 'unit' => __('pkg'), 'who' => '⭐ '.__('Resellers'),
            'code' => ['box_package_code', 'packageCode'], 'multiplier' => ['box_quantity', 'packageMultiplier'], 'price' => ['reseller_piece_price', 'packagePiecePrice'],
            'min' => ['reseller_min_quantity', 'packageMin'], 'step' => ['reseller_quantity_step', 'packageStep'], 'base' => false,
        ],
    ];
    $columns = 'md:grid-cols-[minmax(9rem,1.2fr)_minmax(8rem,1fr)_6.5rem_minmax(9rem,1.1fr)_5.5rem_5.5rem_minmax(8rem,1fr)]';
    $cell = 'block text-[11px] font-semibold tracking-wide text-black/50 uppercase md:sr-only';
@endphp

<div class="admin-card overflow-hidden">
    <div class="hidden gap-3 border-b border-black/10 bg-black/[0.025] px-4 py-2.5 md:grid {{ $columns }}">
        @foreach ([__('Unit'), __('Package code'), __('Pieces'), __('Price per piece, so\'m'), __('Min. order'), __('Step'), __('Customers can order:')] as $heading)
            <span class="text-[11px] font-semibold tracking-wide text-black/50 uppercase">{{ rtrim($heading, ':') }}</span>
        @endforeach
    </div>

    <div class="divide-y divide-black/10">
        @foreach ($rows as $row)
            <div class="grid grid-cols-2 gap-3 p-4 md:items-start md:gap-3 md:px-4 md:py-3 {{ $columns }}" wire:key="unit-{{ $row['code'][0] }}">
                <div class="col-span-2 md:col-span-1 md:pt-2">
                    <span class="block font-semibold">{{ $row['title'] }} <span class="font-mono text-xs font-normal text-black/50">({{ $row['unit'] }})</span></span>
                    <span class="block text-xs text-black/50">{{ $row['who'] }}</span>
                </div>

                <label class="col-span-2 sm:col-span-1">
                    <span class="{{ $cell }}">{{ __('Package code') }}</span>
                    <input type="text" inputmode="numeric" name="{{ $row['code'][0] }}" wire:model="{{ $row['code'][1] }}" class="admin-input font-mono">
                    @if ($message = $this->error($row['code'][0]))<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@endif
                </label>

                <label>
                    <span class="{{ $cell }}">{{ __('Pieces') }}</span>
                    @if ($row['multiplier'])
                        <input type="number" min="1" name="{{ $row['multiplier'][0] }}" wire:model.live.debounce.300ms="{{ $row['multiplier'][1] }}" class="admin-input">
                        @if ($message = $this->error($row['multiplier'][0]))<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@endif
                    @else
                        <input type="number" value="1" disabled class="admin-input cursor-not-allowed bg-black/[0.04] text-black/50" title="{{ __('A piece is always 1') }}">
                    @endif
                </label>

                <label>
                    <span class="{{ $cell }}">{{ __('Price per piece, so\'m') }}</span>
                    <input type="text" inputmode="numeric" name="{{ $row['price'][0] }}" wire:model.live.debounce.300ms="{{ $row['price'][1] }}"
                        @unless ($row['base']) placeholder="{{ $this->piecePrice !== '' ? $this->piecePrice.' — '.__('as consumers') : '' }}" @endunless class="admin-input">
                    @unless ($row['base'])
                        <span @class(['mt-1 block rounded px-2 py-1 text-xs', 'bg-brand/10 text-ink' => $this->packagePrice && ! $this->packagePrice['fallback'], 'bg-black/[0.03] text-black/60' => ! $this->packagePrice || $this->packagePrice['fallback']]) wire:key="package-price">
                            @if ($this->packagePrice)
                                {{ __('Package price') }}: <b class="tabular-nums">{{ $this->packagePrice['amount'] }}</b>
                                @if ($this->packagePrice['fallback'])<span class="block">{{ __('at the consumer piece price') }}</span>@endif
                            @else
                                {{ __('Enter pieces per package to see the package price.') }}
                            @endif
                        </span>
                    @endunless
                    @foreach ([$row['price'][0], $row['base'] ? 'consumer_price' : 'reseller_price'] as $field)
                        @if ($message = $this->error($field))<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@endif
                    @endforeach
                </label>

                <label>
                    <span class="{{ $cell }}">{{ __('Min. order') }}</span>
                    <input type="number" min="1" name="{{ $row['min'][0] }}" wire:model.live.debounce.300ms="{{ $row['min'][1] }}" @if ($row['base']) required @else placeholder="1" @endif class="admin-input">
                    @if ($message = $this->error($row['min'][0]))<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@endif
                </label>

                <label>
                    <span class="{{ $cell }}">{{ __('Step') }}</span>
                    <input type="number" min="1" name="{{ $row['step'][0] }}" wire:model.live.debounce.300ms="{{ $row['step'][1] }}" @if ($row['base']) required @else placeholder="1" @endif class="admin-input">
                    @if ($message = $this->error($row['step'][0]))<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@endif
                </label>

                <div class="col-span-2 text-sm md:col-span-1 md:pt-2">
                    <span class="{{ $cell }}">{{ __('Customers can order:') }}</span>
                    <span class="font-mono">{{ $this->preview($this->{$row['min'][1]}, $this->{$row['step'][1]}) }}</span> {{ $row['unit'] }}
                    @if (! $row['base'] && $this->packagePieces)
                        <span class="block text-xs text-black/50">= <span class="font-mono">{{ $this->packagePieces }}</span> {{ __('pcs') }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <p class="border-t border-black/10 bg-black/[0.025] px-4 py-2.5 text-xs text-black/60">
        {{ __('Resellers pay per package: the price per piece × pieces per package. Empty reseller price = consumer price. Empty package min. order or step = 1. The first tap adds the minimum, then each tap adds one step.') }}
    </p>
</div>
