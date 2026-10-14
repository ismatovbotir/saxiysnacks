@php($botUsername = app(\App\Support\TelegramSettings::class)->botUsername())

<x-admin.layout :title="__('Sign in')">
    <div class="mb-6 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-lg bg-brand text-xl font-bold text-ink">S</span>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight">SAXIY</h1>
        <p class="text-sm text-black/60">{{ __('Back office') }}</p>
    </div>

    <div class="admin-card overflow-hidden">
        <nav class="grid grid-cols-2 border-b border-black/10 text-[13px] font-semibold" aria-label="{{ __('Sign-in method') }}">
            @foreach (['email' => __('Email & password'), 'phone' => __('Phone & Telegram code')] as $key => $label)
                <a href="{{ route('admin.login', ['via' => $key]) }}" @if ($method === $key) aria-current="page" @endif
                   @class(['px-3 py-3 text-center', 'border-b-2 border-ink text-ink' => $method === $key, 'text-black/50 hover:text-ink' => $method !== $key])>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($method === 'email')
            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4 p-5 sm:p-6">
                @csrf
                <x-admin.field :label="__('Email')" name="email">
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="admin-input">
                </x-admin.field>
                <x-admin.field :label="__('Password')" name="password">
                    <input type="password" name="password" required autocomplete="current-password" class="admin-input">
                </x-admin.field>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="accent-brand"> {{ __('Remember me') }}
                </label>
                <button class="admin-btn w-full">{{ __('Sign in') }}</button>
            </form>
        @elseif ($codePhone === null)
            <form method="POST" action="{{ route('admin.login.code') }}" class="space-y-4 p-5 sm:p-6">
                @csrf
                <x-admin.field :label="__('Phone number')" name="phone">
                    <input type="tel" name="phone" value="{{ old('phone') }}" required autofocus autocomplete="tel" inputmode="tel" placeholder="+998 90 123 45 67" class="admin-input">
                </x-admin.field>
                <button class="admin-btn w-full">{{ __('Send code via Telegram') }}</button>
                <p class="text-xs leading-relaxed text-black/55">
                    {{ __('First time? Open the bot, send /login and share your phone number — codes then arrive there.') }}
                    @if ($botUsername)<a href="https://t.me/{{ $botUsername }}" target="_blank" rel="noopener" class="font-medium text-ink underline">{{ '@'.$botUsername }}</a>@endif
                </p>
            </form>
        @else
            <div class="p-5 sm:p-6">
                <p class="mb-4 flex items-center justify-between gap-2 rounded-md bg-black/[0.03] px-3 py-2 text-sm ring-1 ring-black/10 ring-inset">
                    <span>{{ \App\Support\Phone::format($codePhone) }}</span>
                    <form method="POST" action="{{ route('admin.login.phone.reset') }}">@csrf<button class="text-[13px] font-medium text-black/60 underline hover:text-ink">{{ __('Change') }}</button></form>
                </p>
                <form method="POST" action="{{ route('admin.login.verify') }}" class="space-y-4">
                    @csrf
                    <x-admin.field :label="__('6-digit code')" name="code">
                        <input type="text" name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" maxlength="6" pattern="\d{6}" placeholder="••••••" class="admin-input text-center font-mono text-xl tracking-[0.5em]">
                    </x-admin.field>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remember" value="1" class="accent-brand"> {{ __('Remember me') }}
                    </label>
                    <button class="admin-btn w-full">{{ __('Sign in') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.login.code') }}" class="mt-3 text-center">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $codePhone }}">
                    <button class="text-[13px] font-medium text-black/60 underline hover:text-ink">{{ __('Send a new code') }}</button>
                </form>
            </div>
        @endif
    </div>
</x-admin.layout>
