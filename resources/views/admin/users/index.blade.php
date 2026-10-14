<x-admin.layout :title="__('Admins')">
    <x-admin.page-header :title="__('Admins')" :subtitle="__('People who can sign in to this panel. Every admin has full access.')">
        <a href="{{ route('admin.users.create') }}" class="admin-btn">+ {{ __('New admin') }}</a>
    </x-admin.page-header>

    <div class="admin-card divide-y divide-black/5">
        @foreach ($users as $user)
            <div class="flex flex-wrap items-center gap-3 px-4 py-3">
                <div class="min-w-0 flex-1">
                    <span class="font-semibold">{{ $user->name }}</span>
                    @if (auth()->user()->is($user))
                        <span class="ml-1 rounded-full bg-leaf/10 px-2 py-0.5 text-xs font-medium text-leaf-dark">{{ __('you') }}</span>
                    @endif
                    <span class="block truncate text-sm text-black/60">{{ $user->email }}</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-outline px-3 py-1.5">{{ __('Edit') }}</a>
                    @unless (auth()->user()->is($user))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@js(__('Delete this admin?')))">
                            @csrf @method('DELETE')
                            <button class="admin-btn-outline px-3 py-1.5 text-red-700">{{ __('Delete') }}</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</x-admin.layout>
