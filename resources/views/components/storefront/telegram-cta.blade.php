@props(['url'])

@if ($url)
  <div {{ $attributes->class('flex flex-col gap-4 rounded-[26px] bg-sun p-6 md:flex-row md:items-center md:justify-between md:gap-7 md:p-7') }}>
    <div>
      <h2 class="text-xl md:text-[28px]">{{ __('site.tgH') }}</h2>
      <p class="mt-2 max-w-[60ch] text-muted">{{ __('site.tgP') }}</p>
    </div>
    <a class="btn btn-telegram shrink-0" href="{{ $url }}" target="_blank" rel="noopener">{{ __('site.tgBtn') }}</a>
  </div>
@endif
