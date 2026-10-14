@props(['seo'])

@php
    use App\Support\Storefront;

    $alternates = Storefront::alternates($seo['route'], $seo['params'] ?? []);
    $canonical = $alternates[$locale];
    $title = $seo['title'];
    $description = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $seo['description'])), 158, '…', preserveWords: true);
    $image = $seo['image'] ?? $site->ogImageUrl();
    $breadcrumbs = $seo['breadcrumbs'] ?? [];
    $schema = app(\App\Support\StructuredData::class);
    $graph = array_values(array_filter([
        $schema->organization(),
        $schema->website(),
        $breadcrumbs ? $schema->breadcrumbs($breadcrumbs) : null,
        ...($seo['schema'] ?? []),
    ]));
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    $current = request()->route()?->getName();
    $navActive = match (true) {
        str_ends_with((string) $current, 'home') => 'home',
        str_ends_with((string) $current, 'contact') => 'contact',
        default => 'catalog',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
@foreach ($alternates as $alternateLocale => $alternateUrl)
<link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $alternates[Storefront::DEFAULT_LOCALE] }}">
<meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
<meta property="og:site_name" content="{{ $site->get('brand_name') }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ Storefront::ogLocale($locale) }}">
@foreach (array_diff(Storefront::LOCALES, [$locale]) as $otherLocale)
<meta property="og:locale:alternate" content="{{ Storefront::ogLocale($otherLocale) }}">
@endforeach
@if ($image)
<meta property="og:image" content="{{ $image }}">
<meta name="twitter:card" content="summary_large_image">
@else
<meta name="twitter:card" content="summary">
@endif
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="theme-color" content="#F59B00">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=Unbounded:wght@500;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
<script type="application/ld+json">{!! $jsonLd !!}</script>
<script>window.SAXIY={!! json_encode([
    'L' => [
        'added' => __('site.added'), 'empty' => __('site.empty'), 'boxes' => __('site.boxes'), 'pieces' => __('site.pieces'),
        'g' => __('site.g'), 'sendTg' => __('site.sendTg'), 'toCall' => __('site.toCall'), 'clear' => __('site.clear'),
        'order' => __('site.order'), 'hi' => __('site.hi'), 'errName' => __('site.errName'), 'errPhone' => __('site.errPhone'),
        'cName' => __('site.cName'), 'cPhone' => __('site.cPhone'), 'cCompany' => __('site.cCompany'),
    ],
    'products' => $cartProducts,
    'tg' => $site->telegramChatUrl(),
    'tel' => $site->primaryPhone(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!};</script>
</head>
<body class="min-h-dvh bg-paper pb-[calc(4rem+env(safe-area-inset-bottom))] font-sans text-base/relaxed text-ink antialiased md:pb-0">
<a class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-full focus:bg-surface focus:px-4 focus:py-2" href="#main">Skip to content</a>

{{-- No backdrop blur below md: it would become the containing block of the fixed bottom nav. --}}
<header class="sticky top-0 z-30 border-b border-line bg-paper pt-[env(safe-area-inset-top)] md:bg-paper/90 md:backdrop-blur-md">
  <div class="mx-auto flex h-14 max-w-[1180px] items-center gap-3 px-4 md:h-16 md:gap-5 md:px-5">
    <a class="flex shrink-0 items-center gap-2" href="{{ Storefront::route('home') }}" aria-label="{{ $site->get('brand_name') }} — {{ __('site.home') }}">
      <svg class="size-8 md:size-9" viewBox="0 0 60 60" aria-hidden="true"><path d="M30 3a27 27 0 1 0 22 42 23 23 0 0 1-18 8A24 24 0 0 1 30 3Z" fill="#F59B00"/><path d="M14 29C18 17 32 9 46 20c-14-3-26 0-32 9Z" fill="#2FA354"/></svg>
      <b class="font-wordmark text-xl font-bold tracking-wider md:text-2xl">{{ $site->get('brand_name') }}</b>
    </a>

    <nav aria-label="Main" class="fixed inset-x-0 bottom-0 z-40 flex justify-around gap-1 border-t border-line bg-surface px-1.5 pt-1.5 pb-[calc(0.375rem+env(safe-area-inset-bottom))] md:static md:ml-auto md:justify-start md:border-0 md:bg-transparent md:p-0">
      @foreach (['home' => __('site.home'), 'catalog' => __('site.catalog'), 'contact' => __('site.contact')] as $key => $label)
        <a href="{{ Storefront::route($key) }}" @if ($navActive === $key) aria-current="page" @endif
           class="flex-1 rounded-full px-3 py-2 text-center text-[13px] font-medium hover:bg-tint aria-[current=page]:bg-tint md:flex-none md:text-[15px]">{{ $label }}</a>
      @endforeach
    </nav>

    <div class="ml-auto flex items-center gap-2 md:ml-0">
      <div class="flex overflow-hidden rounded-full border border-line" role="group" aria-label="Language">
        @foreach ($alternates as $alternateLocale => $alternateUrl)
          <a href="{{ $alternateUrl }}" hreflang="{{ $alternateLocale }}" lang="{{ $alternateLocale }}" @if ($alternateLocale === $locale) aria-current="true" @endif
             class="px-2.5 py-1.5 text-xs font-semibold aria-[current=true]:bg-ink aria-[current=true]:text-paper">{{ strtoupper($alternateLocale) }}</a>
        @endforeach
      </div>
      <button id="cartBtn" type="button" aria-label="{{ __('site.dTitle') }}" class="flex h-10 items-center gap-2 rounded-full border border-line bg-surface px-3 text-sm font-semibold">
        <span class="hidden sm:inline">{{ __('site.inq') }}</span>
        <span id="cartN" class="grid h-[22px] min-w-[22px] place-items-center rounded-full bg-brand px-1.5 text-xs text-night">0</span>
      </button>
    </div>
  </div>
</header>

<main id="main">
  @if ($breadcrumbs)
    <nav class="mx-auto max-w-[1180px] px-4 pt-4 pb-1 text-sm text-muted md:px-5 md:pt-5" aria-label="Breadcrumb">
      @foreach ($breadcrumbs as [$crumbName, $crumbUrl])
        @if ($loop->last)<span aria-current="page">{{ $crumbName }}</span>@else<a class="no-underline hover:underline" href="{{ $crumbUrl }}">{{ $crumbName }}</a> / @endif
      @endforeach
    </nav>
  @endif
  {{ $slot }}
</main>

<footer class="mt-5 bg-night pt-10 pb-8 text-[#DCE6D2] md:pt-11">
  <div class="mx-auto grid max-w-[1180px] gap-7 px-4 md:grid-cols-[1.3fr_1fr_1fr] md:px-5">
    <div>
      <h3 class="mb-2.5 text-[15px] text-white">{{ $site->get('brand_name') }} Premium Snacks</h3>
      <p class="max-w-[38ch] opacity-85">{{ $site->text('tagline') }}</p>
      @if ($site->socialLinks())
        <div class="mt-3 flex flex-wrap gap-2" aria-label="{{ __('site.followUs') }}">
          @foreach ($site->socialLinks() as $network => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener me" class="rounded-full border border-white/20 px-3 py-1.5 text-sm hover:border-brand hover:text-brand">{{ \App\Support\SiteSettings::SOCIAL_NETWORKS[$network] }}</a>
          @endforeach
        </div>
      @endif
    </div>
    <div>
      <h3 class="mb-2.5 text-[15px] text-white">{{ __('site.fShop') }}</h3>
      @foreach ($navCategories as $navCategory)
        <a class="block py-1 hover:text-brand" href="{{ Storefront::route('category', $navCategory) }}">{{ $navCategory->translate('name') }}</a>
      @endforeach
    </div>
    <div>
      <h3 class="mb-2.5 text-[15px] text-white">{{ __('site.fContact') }}</h3>
      @foreach ($site->get('phones') as $phone)
        <a class="block py-1 hover:text-brand" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>
      @endforeach
      @if ($site->get('email'))<a class="block py-1 hover:text-brand" href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif
      <address class="pt-1.5 text-sm not-italic opacity-85">{{ $site->text('legal_name') }}<br>{{ $site->text('address') }}@if ($site->text('working_hours'))<br>{{ $site->text('working_hours') }}@endif</address>
    </div>
  </div>
  <div class="mx-auto mt-6 max-w-[1180px] px-4 md:px-5">
    <div class="border-t border-white/15 pt-4 text-[13px] opacity-75">© {{ now()->year }} {{ $site->get('brand_name') }}@if ($site->get('founded_year')) · Est. {{ $site->get('founded_year') }}@endif</div>
  </div>
</footer>

<div id="scrim" class="fixed inset-0 z-[60] hidden bg-black/50 data-[open]:block"></div>
<aside id="drawer" aria-label="{{ __('site.dTitle') }}" aria-hidden="true"
       class="fixed inset-y-0 right-0 z-[61] flex w-full max-w-[420px] translate-x-full flex-col bg-surface pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)] transition-transform duration-300 data-[open]:translate-x-0">
  <header class="flex items-center justify-between border-b border-line px-5 py-4">
    <h3 class="text-lg">{{ __('site.dTitle') }}</h3>
    <button class="btn btn-outline btn-sm" id="dClose" type="button">{{ __('site.close') }}</button>
  </header>
  <div class="flex-1 overflow-auto px-5 py-2" id="dList"></div>
  <footer class="grid gap-2.5 border-t border-line px-5 py-4 empty:hidden" id="dFoot"></footer>
</aside>
<div id="toast" role="status" class="pointer-events-none fixed bottom-24 left-1/2 z-[80] -translate-x-1/2 translate-y-5 rounded-full bg-ink px-4 py-2.5 text-sm font-semibold text-paper opacity-0 transition data-[on]:translate-y-0 data-[on]:opacity-100 md:bottom-8"></div>
</body>
</html>
