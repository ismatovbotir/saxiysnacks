@props(['title'])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1b2616">
    <title>{{ $title }} · SAXIY Admin</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-paper font-sans text-ink antialiased">
    @auth
        {{-- Desktop sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-ink text-white lg:flex">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-5 py-5 text-lg font-bold tracking-wide">
                <span class="grid size-8 place-items-center rounded-lg bg-brand text-sm text-ink">S</span>
                SAXIY <span class="text-sm font-normal text-white/50">CRM</span>
            </a>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-2" aria-label="{{ __('Main menu') }}">
                @include('components.admin.partials.nav-groups', ['dark' => true])
            </nav>
            <div class="border-t border-white/10 p-3">
                <x-admin.locale-switcher dark class="mx-2 mb-2" />
                <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/10 text-sm font-semibold">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1 text-sm">
                        <a href="{{ route('admin.users.edit', auth()->user()) }}" class="block truncate font-semibold hover:underline">{{ auth()->user()->name }}</a>
                        <span class="block truncate text-xs text-white/50">{{ auth()->user()->email }}</span>
                    </span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white" title="{{ __('Log out') }}" aria-label="{{ __('Log out') }}"><x-admin.icon name="logout" /></button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">
            {{-- Top bar (no backdrop blur: it would trap the fixed tab bar on mobile) --}}
            <header class="sticky top-0 z-20 border-b border-black/10 bg-white/95">
                <div class="mx-auto flex h-14 max-w-7xl items-center gap-3 px-4 lg:px-8">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold lg:hidden">
                        <span class="grid size-7 place-items-center rounded-lg bg-brand text-xs text-ink">S</span>
                        <span class="hidden min-[400px]:inline">SAXIY</span>
                    </a>
                    <form method="GET" action="{{ route('admin.clients.index') }}" class="relative min-w-0 flex-1 md:max-w-md" role="search">
                        <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-black/40" />
                        <input type="search" name="search" value="{{ request()->routeIs('admin.clients.index') ? request('search') : '' }}" placeholder="{{ __('Find a customer: name, phone, VAT…') }}" aria-label="{{ __('Search customers') }}"
                               class="w-full rounded-full border border-black/10 bg-paper py-2 pr-3 pl-9 text-sm outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30">
                    </form>
                    <a href="{{ route('home') }}" target="_blank" class="hidden items-center gap-1.5 rounded-full px-3 py-2 text-sm text-black/60 hover:bg-black/5 hover:text-ink sm:inline-flex">
                        {{ __('View site') }} <x-admin.icon name="external" class="size-4" />
                    </a>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 pt-5 pb-[calc(6rem+env(safe-area-inset-bottom))] lg:px-8 lg:pt-8 lg:pb-12">
                @include('components.admin.partials.flash')
                {{ $slot }}
            </main>
        </div>

        {{-- Mobile bottom tab bar --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-black/10 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="{{ __('Quick menu') }}">
            <div class="grid grid-cols-5">
                @foreach (collect($navigation)->flatten(1)->where('mobileTab', true) as $item)
                    <a href="{{ $item['url'] }}" @class(['relative flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium', 'text-ink' => $item['active'], 'text-black/50' => ! $item['active']]) @if ($item['active']) aria-current="page" @endif>
                        <span @class(['rounded-full px-4 py-0.5', 'bg-brand/20' => $item['active']])><x-admin.icon :name="$item['icon']" /></span>
                        {{ $item['label'] }}
                        @if ($item['badge'])
                            <span class="absolute top-1 left-1/2 ml-2 min-w-4.5 rounded-full bg-red-600 px-1 text-center text-[10px] leading-4.5 font-bold text-white">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
                <button type="button" data-admin-drawer-open @class(['flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium', 'text-ink' => $navigationMoreActive, 'text-black/50' => ! $navigationMoreActive]) aria-controls="admin-drawer" aria-expanded="false">
                    <span @class(['rounded-full px-4 py-0.5', 'bg-brand/20' => $navigationMoreActive])><x-admin.icon name="menu" /></span>
                    {{ __('More') }}
                </button>
            </div>
        </nav>

        {{-- Mobile "More" drawer with the full grouped menu --}}
        <div id="admin-drawer" class="group/drawer lg:hidden" data-admin-drawer>
            <div data-admin-drawer-close class="pointer-events-none fixed inset-0 z-40 bg-black/40 opacity-0 transition-opacity group-data-[open]/drawer:pointer-events-auto group-data-[open]/drawer:opacity-100"></div>
            <div role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}"
                 class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] translate-y-full overflow-y-auto rounded-t-2xl bg-ink pb-[calc(1rem+env(safe-area-inset-bottom))] text-white transition-transform group-data-[open]/drawer:translate-y-0">
                <div class="sticky top-0 flex items-center justify-between bg-ink px-5 pt-4 pb-2">
                    <span class="font-bold">{{ __('Menu') }}</span>
                    <button type="button" data-admin-drawer-close class="rounded-lg p-2 text-white/70 hover:bg-white/10" aria-label="{{ __('Close menu') }}"><x-admin.icon name="close" /></button>
                </div>
                <nav class="space-y-5 px-3 py-2" aria-label="{{ __('All sections') }}">
                    @include('components.admin.partials.nav-groups', ['dark' => true])
                </nav>
                <x-admin.locale-switcher dark class="mx-6 mt-4" />
                <div class="mx-3 mt-4 flex items-center gap-2 border-t border-white/10 pt-4">
                    <a href="{{ route('home') }}" target="_blank" class="flex flex-1 items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-white/70 hover:bg-white/10">{{ __('View site') }} <x-admin.icon name="external" class="size-4" /></a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-white/70 hover:bg-white/10"><x-admin.icon name="logout" class="size-4" /> {{ __('Log out') }} ({{ auth()->user()->name }})</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const drawer = document.querySelector('[data-admin-drawer]');
                const opener = document.querySelector('[data-admin-drawer-open]');
                const toggle = (open) => { drawer.toggleAttribute('data-open', open); opener.setAttribute('aria-expanded', open); document.body.classList.toggle('overflow-hidden', open); };
                opener.addEventListener('click', () => toggle(true));
                drawer.querySelectorAll('[data-admin-drawer-close]').forEach((el) => el.addEventListener('click', () => toggle(false)));
                document.addEventListener('keydown', (event) => event.key === 'Escape' && toggle(false));
            })();
        </script>
    @else
        <main class="grid min-h-screen place-items-center px-4 py-10">
            <div class="w-full max-w-sm">
                <div class="mb-4 flex justify-center"><x-admin.locale-switcher /></div>
                @include('components.admin.partials.flash')
                {{ $slot }}
            </div>
        </main>
    @endauth
</body>
</html>
