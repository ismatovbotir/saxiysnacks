<x-admin.layout :title="__('Staff')">
    <x-admin.page-header :title="__('Staff')" :subtitle="__('Staff who can sign in. The role decides which sections they see.')">
        <a href="{{ route('admin.users.create') }}" class="admin-btn">+ {{ __('New staff member') }}</a>
    </x-admin.page-header>

    <div class="admin-card divide-y divide-black/5">
        @foreach ($users as $user)
            <div class="flex flex-wrap items-center gap-3 px-4 py-3">
                <div class="min-w-0 flex-1">
                    <span class="font-semibold">{{ $user->name }}</span>
                    @if (auth()->user()->is($user))
                        <span class="ml-1 admin-badge ring-black/5 bg-leaf/10 text-xs font-medium text-leaf-dark">{{ __('you') }}</span>
                    @endif
                    <span class="block truncate text-sm text-black/60">{{ $user->email }}@if ($user->phone) · {{ \App\Support\Phone::format($user->phone) }}@endif · <span class="font-medium text-ink">{{ $user->role->label() }}</span></span>
                    <span class="mt-0.5 flex gap-1.5 text-[11px]">
                        <span class="admin-badge bg-black/[0.03] text-black/65 ring-black/10">{{ __('Email login') }}</span>
                        @if ($user->hasTelegramLinked())<span class="admin-badge bg-green-50 text-green-800 ring-green-600/20">{{ __('Telegram code') }}</span>@endif
                    </span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-outline h-8 px-2.5">{{ __('Edit') }}</a>
                    @unless (auth()->user()->is($user))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@js(__('Delete this staff member?')))">
                            @csrf @method('DELETE')
                            <button class="admin-btn-danger h-8 px-2.5">{{ __('Delete') }}</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</x-admin.layout>
