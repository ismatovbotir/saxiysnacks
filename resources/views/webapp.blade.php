<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>SAXIY</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
@vite(['resources/css/webapp.css', 'resources/js/webapp.js'])
</head>
<body class="min-h-dvh pb-[calc(4.5rem+env(safe-area-inset-bottom))]">
<main id="app" class="mx-auto w-full max-w-2xl px-3 py-3 sm:px-4" data-api="{{ url('/api/webapp') }}">
    <p class="py-16 text-center text-tg-hint">…</p>
</main>
<nav id="tabs" class="fixed inset-x-0 bottom-0 z-10 border-t border-tg-text/10 bg-tg-surface pb-[env(safe-area-inset-bottom)]" hidden></nav>
</body>
</html>
