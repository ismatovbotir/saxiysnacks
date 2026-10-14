@props(['title', 'subtitle' => null])

<div class="mb-5 border-b border-black/10 pb-4 lg:mb-6">
    @isset($navigationCurrent)
        <nav class="mb-1.5 flex items-center gap-1 text-xs font-medium text-black/45" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-ink">SAXIY</a>
            @if ($navigationCurrent['group'])
                <x-admin.icon name="chevron" class="size-3" />
                <span>{{ $navigationCurrent['group'] }}</span>
            @endif
            @if ($navigationCurrent['label'] !== $title)
                <x-admin.icon name="chevron" class="size-3" />
                <a href="{{ $navigationCurrent['url'] }}" class="hover:text-ink hover:underline">{{ $navigationCurrent['label'] }}</a>
            @endif
        </nav>
    @endisset
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="truncate text-xl font-semibold tracking-tight sm:text-[22px]">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-0.5 text-[13px] text-black/55">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($slot->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
        @endif
    </div>
</div>
