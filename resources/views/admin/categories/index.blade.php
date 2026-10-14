<x-admin.layout title="Categories">
    <x-admin.page-header title="Categories">
        <a href="{{ route('admin.categories.create') }}" class="admin-btn">+ New category</a>
    </x-admin.page-header>

    <div class="admin-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10 text-black/60">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="mr-2 inline-block size-3 rounded-full align-middle" style="background: {{ $category->color }}"></span>
                            <span class="font-semibold">{{ $category->translate('name', 'ru') }}</span>
                            <span class="block text-xs text-black/50">{{ $category->translate('name', 'uz') }} · {{ $category->translate('name', 'en') }}</span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $category->slug }}</td>
                        <td class="px-4 py-3"><a class="underline" href="{{ route('admin.products.index', ['category' => $category->id]) }}">{{ $category->products_count }}</a></td>
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">{{ $category->is_active ? 'Visible' : 'Hidden' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="admin-btn-outline px-3 py-1.5">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="admin-btn-outline px-3 py-1.5 text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-black/50">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.layout>
