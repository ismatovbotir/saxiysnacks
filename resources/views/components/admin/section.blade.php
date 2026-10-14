@props(['title', 'description' => null, 'wide' => false])

{{-- Form section: title and help text on the left on wide screens, fields on the right; wide sections (tables) stack. --}}
<section {{ $attributes->class(['grid gap-x-8 gap-y-3 border-b border-black/10 py-5 first-of-type:pt-0 last-of-type:border-b-0', 'lg:grid-cols-[15rem_minmax(0,1fr)]' => ! $wide]) }}>
    <div>
        <h2 class="text-sm font-semibold">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-xs leading-relaxed text-black/55">{{ $description }}</p>
        @endif
    </div>
    <div class="min-w-0 space-y-4">{{ $slot }}</div>
</section>
