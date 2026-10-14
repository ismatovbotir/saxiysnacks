@props(['title', 'subtitle' => null])

<div class="mb-5 lg:mb-6">
    @isset($navigationCurrent)
        <nav class="mb-1 flex items-center gap-1 text-xs font-medium text-black/50" aria-label="{{ __('Breadcrumb') }}">
            <span>{{ $navigationCurrent['group'] }}</span>
            @if ($navigationCurrent['label'] !== $title)
                <x-admin.icon name="chevron" class="size-3" />
                <a href="{{ $navigationCurrent['url'] }}" class="hover:text-ink hover:underline">{{ $navigationCurrent['label'] }}</a>
            @endif
        </nav>
    @endisset
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="truncate text-xl font-bold sm:text-2xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-black/60">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($slot->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
        @endif
    </div>
</div>
