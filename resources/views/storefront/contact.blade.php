@php
    use App\Support\Storefront;

    $seo = [
        'title' => __('site.metaContactTitle'),
        'description' => __('site.cP').' '.$site->text('address').' · '.collect($site->get('phones'))->implode(', '),
        'route' => 'contact',
        'breadcrumbs' => [[__('site.home'), Storefront::route('home')], [__('site.contact'), Storefront::route('contact')]],
        'schema' => [['@type' => 'ContactPage', 'name' => __('site.contact'), 'url' => Storefront::route('contact'), 'about' => ['@id' => url('/').'#organization']]],
    ];
@endphp

<x-storefront.layout :seo="$seo">
  @php($row = 'flex items-center gap-3.5 rounded-[18px] border border-line bg-surface px-4 py-4 md:px-5')
  @php($icon = 'grid size-11 shrink-0 place-items-center rounded-full bg-tint text-lg')
  <div class="mx-auto max-w-[1180px] px-4 pb-14 md:px-5">
    <div class="pt-2 pb-6">
      <h1 class="text-[28px] md:text-[44px]">{{ __('site.cH') }}</h1>
      <p class="mt-2.5 max-w-[52ch] text-muted">{{ __('site.cP') }}</p>
    </div>
    <div class="grid gap-8 md:grid-cols-[.9fr_1.1fr]">
      <div class="grid content-start gap-3">
        @foreach ($site->get('phones') as $phone)
          <a class="{{ $row }}" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}"><span class="{{ $icon }}" aria-hidden="true">☎</span><span><b class="block">{{ __('site.cCall') }}</b>{{ $phone }}</span></a>
        @endforeach
        @if ($site->get('telegram_contact'))
          <a class="{{ $row }}" href="{{ $site->telegramChatUrl(__('site.hi')) }}" target="_blank" rel="noopener"><span class="{{ $icon }}" aria-hidden="true">✈</span><span><b class="block">{{ __('site.cTg') }}</b>{{ '@'.$site->get('telegram_contact') }}</span></a>
        @endif
        @if ($telegramBotUrl)
          <a class="{{ $row }}" href="{{ $telegramBotUrl }}" target="_blank" rel="noopener"><span class="{{ $icon }}" aria-hidden="true">🛒</span><span><b class="block">{{ __('site.tgH') }}</b>{{ __('site.tgBtn') }}</span></a>
        @endif
        @if ($site->get('email'))
          <a class="{{ $row }}" href="mailto:{{ $site->get('email') }}"><span class="{{ $icon }}" aria-hidden="true">@</span><span><b class="block">{{ __('site.email') }}</b>{{ $site->get('email') }}</span></a>
        @endif
        <div class="{{ $row }}"><span class="{{ $icon }}" aria-hidden="true">⌂</span><span><b class="block">{{ __('site.cAddr') }}</b>{{ $site->text('legal_name') }}<br>{{ $site->text('address') }}@if ($site->get('map_url'))<br><a href="{{ $site->get('map_url') }}" target="_blank" rel="noopener" class="underline">{{ __('site.map') }}</a>@endif</span></div>
        @if ($site->text('working_hours'))
          <div class="{{ $row }}"><span class="{{ $icon }}" aria-hidden="true">◷</span><span><b class="block">{{ __('site.hours') }}</b>{{ $site->text('working_hours') }}</span></div>
        @endif
        @if ($site->socialLinks())
          <div class="{{ $row }}"><span class="{{ $icon }}" aria-hidden="true">★</span><span><b class="block">{{ __('site.followUs') }}</b>
            <span class="flex flex-wrap gap-x-3">@foreach ($site->socialLinks() as $network => $url)<a href="{{ $url }}" target="_blank" rel="noopener me" class="underline">{{ \App\Support\SiteSettings::SOCIAL_NETWORKS[$network] }}</a>@endforeach</span>
          </span></div>
        @endif
      </div>

      <form id="cf" novalidate class="grid content-start gap-4 rounded-[26px] border border-line bg-surface p-5 md:p-7">
        <div class="grid gap-3 sm:grid-cols-2">
          <label class="grid gap-1.5 text-sm font-semibold">{{ __('site.cName') }}<input id="cn" autocomplete="name" class="field"><span class="min-h-4 text-[13px] text-red-600" id="e1"></span></label>
          <label class="grid gap-1.5 text-sm font-semibold">{{ __('site.cPhone') }}<input id="cp" type="tel" autocomplete="tel" placeholder="+998" class="field"><span class="min-h-4 text-[13px] text-red-600" id="e2"></span></label>
        </div>
        <label class="grid gap-1.5 text-sm font-semibold">{{ __('site.cCompany') }}<input id="cc" autocomplete="organization" class="field"></label>
        <div>
          <div class="mb-2 text-sm font-semibold">{{ __('site.cType') }}</div>
          <div class="flex flex-wrap gap-2" id="ct">
            @foreach (['ret', 'hor', 'dist', 'oth'] as $type)
              <button type="button" class="chip" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" data-t="{{ __('site.t_'.$type) }}">{{ __('site.t_'.$type) }}</button>
            @endforeach
          </div>
        </div>
        <label class="grid gap-1.5 text-sm font-semibold">{{ __('site.cMsg') }}<textarea id="cm" rows="4" class="field"></textarea></label>
        <button class="btn btn-telegram" type="submit">{{ __('site.cSend') }}</button>
      </form>
    </div>
  </div>
</x-storefront.layout>
