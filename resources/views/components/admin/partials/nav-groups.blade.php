{{-- Dashboard, then collapsible groups of the pages this role may open. The group with the open page stays expanded. --}}
@include('components.admin.partials.nav-item', ['item' => $navigationHome])

@foreach ($navigation as $group => $items)
    <details class="group/nav" open data-nav-group="{{ $group }}">
        <summary class="mb-1 flex cursor-pointer list-none items-center justify-between rounded px-2.5 py-1 text-[10px] font-semibold tracking-[0.14em] text-white/40 uppercase hover:text-white/70 [&::-webkit-details-marker]:hidden">
            {{ $group }}
            <x-admin.icon name="chevron" class="size-3 transition-transform group-open/nav:rotate-90" />
        </summary>
        <ul class="space-y-0.5">
            @foreach ($items as $item)
                <li>@include('components.admin.partials.nav-item')</li>
            @endforeach
        </ul>
    </details>
@endforeach
