<x-admin.layout :title="$user->exists ? __('Edit staff member') : __('New staff member')">
    <x-admin.page-header :title="$user->exists ? __('Edit staff member') : __('New staff member')">
        <a href="{{ route('admin.users.index') }}" class="admin-btn-outline hidden sm:inline-flex">← {{ __('Back') }}</a>
    </x-admin.page-header>

    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="admin-card max-w-xl space-y-5 p-4 sm:p-6">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <x-admin.field :label="__('Name').' *'" name="name">
            <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="admin-input">
        </x-admin.field>
        <x-admin.field :label="__('Email (login)').' *'" name="email">
            <input type="email" name="email" required autocomplete="off" value="{{ old('email', $user->email) }}" class="admin-input">
        </x-admin.field>
        <x-admin.field :label="__('Phone (for Telegram sign-in)')" name="phone" :hint="__('After saving, the person opens the bot, sends /login and shares this number. Then they can sign in with a code.')">
            <input type="tel" name="phone" inputmode="tel" autocomplete="off" value="{{ old('phone', $user->phone ? \App\Support\Phone::format($user->phone) : '') }}" placeholder="+998 90 123 45 67" class="admin-input">
        </x-admin.field>
        @if ($user->exists && $user->phone)
            <div class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-black/[0.03] px-3 py-2 text-sm ring-1 ring-black/10 ring-inset">
                @if ($user->hasTelegramLinked())
                    <span class="flex items-center gap-1.5 text-leaf-dark"><x-admin.icon name="send" class="size-4" /> {{ __('Telegram linked — codes can be sent.') }}</span>
                    <label class="flex items-center gap-2 text-[13px]"><input type="checkbox" name="unlink_telegram" value="1" class="accent-ink"> {{ __('Unlink') }}</label>
                @else
                    <span class="flex items-center gap-1.5 text-amber-800"><x-admin.icon name="alert" class="size-4" /> {{ __('Telegram not linked yet: the person must send /login to the bot and share this number.') }}</span>
                @endif
            </div>
        @endif
        <x-admin.field :label="__('Role').' *'" name="role">
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach (\App\Enums\UserRole::staff() as $role)
                    <label class="flex cursor-pointer items-start gap-2.5 rounded-md border border-black/15 p-3 has-checked:border-ink has-checked:bg-black/[0.03]">
                        <input type="radio" name="role" value="{{ $role->value }}" class="mt-0.5 accent-ink" @checked(old('role', $user->role?->value) === $role->value)>
                        <span class="text-sm"><span class="block font-semibold">{{ $role->label() }}</span>
                            <span class="block text-xs text-black/55">{{ $role->description() }}</span></span>
                    </label>
                @endforeach
            </div>
        </x-admin.field>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.field :label="$user->exists ? __('New password') : __('Password').' *'" name="password" :hint="$user->exists ? __('Leave empty to keep the current password.') : __('At least 8 characters.')">
                <input type="password" name="password" autocomplete="new-password" @required(! $user->exists) class="admin-input">
            </x-admin.field>
            <x-admin.field :label="__('Repeat password')" name="password_confirmation">
                <input type="password" name="password_confirmation" autocomplete="new-password" class="admin-input">
            </x-admin.field>
        </div>

        <x-admin.form-actions :cancel="route('admin.users.index')" />
    </form>
</x-admin.layout>
