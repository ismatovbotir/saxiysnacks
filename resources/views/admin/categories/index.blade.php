<x-admin.layout :title="__('Categories')">
    <x-admin.page-header :title="__('Categories')" :subtitle="__('Groups on the website catalog and in the Telegram app.')">
        <a href="{{ route('admin.categories.create') }}" class="admin-btn"><x-admin.icon name="plus" class="size-4" /> {{ __('New category') }}</a>
    </x-admin.page-header>

    <div class="space-y-2 md:hidden">
        @forelse ($categories as $category)
            <div class="admin-card flex items-center gap-3 p-4">
                <span class="size-4 shrink-0 rounded-full" style="background: {{ $category->color }}"></span>
                <a href="{{ route('admin.categories.edit', $category) }}" class="min-w-0 flex-1">
                    <span class="block truncate font-semibold">{{ $category->translate('name', app()->getLocale()) }}</span>
                    <span class="block truncate text-xs text-black/50">{{ collect(['uz', 'ru', 'en'])->reject(app()->getLocale())->map(fn ($locale) => $category->translate('name', $locale))->filter()->unique()->implode(' · ') }}</span>
                </a>
                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="shrink-0 admin-badge ring-black/5 bg-black/5 text-xs font-medium">{{ trans_choice(':count product|:count products', $category->products_count) }}</a>
                @unless ($category->is_active)<span class="shrink-0 text-xs text-black/50">{{ __('Hidden') }}</span>@endunless
            </div>
        @empty
            <p class="admin-card px-4 py-10 text-center text-sm text-black/50">{{ __('No categories yet.') }}</p>
        @endforelse
    </div>

    <div class="admin-card hidden overflow-x-auto md:block">
        <table class="admin-table">
            <thead>
                <tr><th class="admin-th">{{ __('Name') }}</th><th class="admin-th">{{ __('Page address') }}</th><th class="admin-th">{{ __('Products') }}</th><th class="admin-th">{{ __('Sort order') }}</th><th class="admin-th">{{ __('Status') }}</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="admin-td">
                            <span class="mr-2 inline-block size-3 rounded-full align-middle" style="background: {{ $category->color }}"></span>
                            <span class="font-semibold">{{ $category->translate('name', app()->getLocale()) }}</span>
                            <span class="block text-xs text-black/50">{{ collect(['uz', 'ru', 'en'])->reject(app()->getLocale())->map(fn ($locale) => $category->translate('name', $locale))->filter()->unique()->implode(' · ') }}</span>
                        </td>
                        <td class="admin-td font-mono text-xs">{{ $category->slug }}</td>
                        <td class="admin-td"><a class="underline" href="{{ route('admin.products.index', ['category' => $category->id]) }}">{{ $category->products_count }}</a></td>
                        <td class="admin-td">{{ $category->sort_order }}</td>
                        <td class="admin-td">{{ $category->is_active ? __('Visible') : __('Hidden') }}</td>
                        <td class="admin-td text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="admin-btn-outline h-8 px-2.5">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm(@js(__('Delete this category?')))">
                                @csrf @method('DELETE')
                                <button class="admin-btn-danger h-8 px-2.5">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-black/50">{{ __('No categories yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.layout>
