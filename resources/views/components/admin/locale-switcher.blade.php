@props(['dark' => false])

{{-- Admin panel language: UZ · RU · EN --}}
<form method="POST" action="{{ route('admin.locale') }}" {{ $attributes->class(['inline-flex rounded-md p-0.5 text-[11px] font-semibold', 'bg-white/10' => $dark, 'bg-black/5' => ! $dark]) }}>
    @csrf @method('PUT')
    @foreach (\App\Http\Middleware\SetAdminLocale::LOCALES as $locale => $language)
        <button name="locale" value="{{ $locale }}" title="{{ $language }}" lang="{{ $locale }}"
                @if (app()->getLocale() === $locale) aria-current="true" @endif
                @class([
                    'rounded px-2 py-1 uppercase',
                    'bg-white text-ink shadow-sm' => app()->getLocale() === $locale,
                    'text-white/60 hover:text-white' => $dark && app()->getLocale() !== $locale,
                    'text-black/50 hover:text-ink' => ! $dark && app()->getLocale() !== $locale,
                ])>{{ $locale }}</button>
    @endforeach
</form>
