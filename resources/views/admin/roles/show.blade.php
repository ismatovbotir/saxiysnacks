<x-admin.layout :title="$role->label()">
    <x-admin.page-header :title="$role->label()" :subtitle="$role->description()">
        <a href="{{ route('admin.roles.index') }}" class="admin-btn-outline">← {{ __('All roles') }}</a>
    </x-admin.page-header>

    {{-- Role switcher: every role is its own page --}}
    <nav class="admin-chips mb-5" aria-label="{{ __('Roles') }}">
        @foreach (\App\Enums\UserRole::cases() as $case)
            <a href="{{ route('admin.roles.show', $case) }}" class="admin-chip" @if ($case === $role) aria-current="page" @endif>{{ $case->label() }}</a>
        @endforeach
    </nav>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-6">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="admin-card p-4 sm:p-6">
            @csrf @method('PUT')

            @unless ($editable)
                <p class="mb-5 flex items-start gap-2 rounded-md bg-black/[0.03] px-3 py-2.5 text-[13px] text-black/70 ring-1 ring-black/10 ring-inset">
                    <x-admin.icon name="lock" class="mt-px size-4 shrink-0" />
                    {{ $role === \App\Enums\UserRole::Owner ? __('The owner always opens every page; this cannot be changed.') : __('Client accounts never open the back office. They are kept for the future customer cabinet.') }}
                </p>
            @endunless

            @foreach (\App\Enums\AdminPermission::grouped() as $group => $permissions)
                <x-admin.section :title="__($group)">
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label @class([
                                'flex items-start gap-2.5 rounded-md border p-3',
                                'cursor-pointer border-black/15 has-checked:border-ink has-checked:bg-black/[0.03]' => $editable,
                                'border-black/10 opacity-80' => ! $editable,
                            ])>
                                <input type="checkbox" name="permissions[]" value="{{ $permission->value }}" class="mt-0.5 accent-ink"
                                       @checked(in_array($permission, $granted, true)) @disabled(! $editable)>
                                <span class="text-sm">
                                    <span class="flex items-center gap-1.5 font-semibold"><x-admin.icon :name="$permission->icon()" class="size-4 text-black/45" /> {{ $permission->label() }}</span>
                                    <span class="mt-0.5 block text-xs text-black/55">{{ $permission->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-admin.section>
            @endforeach

            @if ($editable)
                <x-admin.form-actions :cancel="route('admin.roles.index')" :label="__('Save access')" />
            @endif
        </form>

        <aside class="admin-card self-start overflow-hidden">
            <div class="admin-card-header">
                <h2 class="admin-card-title">{{ __('People with this role') }}</h2>
                <span class="text-xs text-black/50">{{ $users->count() }}</span>
            </div>
            <ul class="divide-y divide-black/[0.06] text-sm">
                @forelse ($users as $user)
                    <li class="px-4 py-2.5 sm:px-5">
                        @can('access-staff')
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium hover:underline">{{ $user->name }}</a>
                        @else
                            <span class="font-medium">{{ $user->name }}</span>
                        @endcan
                        <span class="block truncate text-xs text-black/50">{{ $user->email ?? $user->phone ?? '—' }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-black/50 sm:px-5">{{ __('Nobody has this role yet.') }}</li>
                @endforelse
            </ul>
            @can('access-staff')
                @if ($role->isStaff())
                    <div class="border-t border-black/10 p-3"><a href="{{ route('admin.users.create', ['role' => $role->value]) }}" class="admin-btn-outline w-full"><x-admin.icon name="plus" class="size-4" /> {{ __('Add a person') }}</a></div>
                @endif
            @endcan
        </aside>
    </div>
</x-admin.layout>
