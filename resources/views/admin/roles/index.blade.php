<x-admin.layout :title="__('Roles & access')">
    <x-admin.page-header :title="__('Roles & access')" :subtitle="__('Each role opens only the pages ticked on its page. Open a role to change its access.')" />

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($roles as ['role' => $role, 'users' => $users, 'permissions' => $permissions])
            <a href="{{ route('admin.roles.show', $role) }}" class="admin-card group flex flex-col p-4 transition hover:border-black/25 hover:shadow-sm sm:p-5">
                <span class="flex items-start justify-between gap-3">
                    <span>
                        <span class="block font-semibold">{{ $role->label() }}</span>
                        <span class="mt-0.5 block text-xs text-black/55">{{ $role->description() }}</span>
                    </span>
                    <x-admin.icon :name="$role === \App\Enums\UserRole::Owner ? 'lock' : 'key'" class="size-4 text-black/30 group-hover:text-ink" />
                </span>
                <span class="mt-4 flex flex-wrap gap-1">
                    @forelse (collect($permissions)->groupBy(fn ($permission) => $permission->group()) as $group => $pages)
                        <span class="admin-badge bg-black/[0.03] text-black/70 ring-black/10">{{ __($group) }} · {{ count($pages) }}</span>
                    @empty
                        <span class="admin-badge bg-red-50 text-red-800 ring-red-600/20">{{ __('No back-office access') }}</span>
                    @endforelse
                </span>
                <span class="mt-auto flex items-center justify-between border-t border-black/[0.06] pt-3 text-[13px] text-black/55" style="margin-top: 1rem">
                    <span>{{ trans_choice(':count person|:count people', $users) }}</span>
                    <span class="font-medium text-ink">{{ __('Open') }} →</span>
                </span>
            </a>
        @endforeach
    </div>
</x-admin.layout>
