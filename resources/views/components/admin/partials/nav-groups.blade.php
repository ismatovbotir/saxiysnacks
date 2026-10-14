@foreach ($navigation as $group => $items)
    <div>
        <p class="mb-1.5 px-3 text-[11px] font-semibold tracking-widest text-white/40 uppercase">{{ $group }}</p>
        <ul class="space-y-0.5">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                           'bg-white/10 text-white' => $item['active'],
                           'text-white/70 hover:bg-white/5 hover:text-white' => ! $item['active'],
                       ])>
                        <x-admin.icon :name="$item['icon']" :class="$item['active'] ? 'text-brand' : ''" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if ($item['badge'])
                            <span class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-ink" title="{{ __('Needs attention') }}">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
