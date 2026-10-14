@props(['title'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · SAXIY Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    @auth
        <header class="border-b border-black/10 bg-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold tracking-wide">
                    <span class="inline-block size-3 rounded-full bg-brand"></span> SAXIY <span class="font-normal text-black/50">admin</span>
                </a>
                <nav class="flex flex-wrap gap-1 text-sm font-medium">
                    @foreach ([
                        'admin.dashboard' => 'Dashboard',
                        'admin.orders.index' => 'Orders',
                        'admin.clients.index' => 'Customers',
                        'admin.products.index' => 'Products',
                        'admin.categories.index' => 'Categories',
                        'admin.settings.edit' => 'Contacts & social',
                        'admin.telegram.edit' => 'Telegram',
                        'admin.users.index' => 'Admins',
                    ] as $routeName => $label)
                        <a href="{{ route($routeName) }}"
                           @class([
                               'rounded-full px-3 py-1.5',
                               'bg-ink text-white' => request()->routeIs(preg_replace('/\.(index|edit)$/', '.*', $routeName)),
                               'hover:bg-black/5' => ! request()->routeIs(preg_replace('/\.(index|edit)$/', '.*', $routeName)),
                           ])>{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="ml-auto flex items-center gap-3 text-sm">
                    <a href="{{ route('home') }}" class="text-black/60 hover:text-ink" target="_blank">View site ↗</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="rounded-full border border-black/15 px-3 py-1.5 hover:bg-black/5">Log out</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main class="mx-auto max-w-7xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-leaf/30 bg-leaf/10 px-4 py-3 text-sm text-leaf-dark">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
