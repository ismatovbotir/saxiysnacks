<x-admin.layout :title="$category->exists ? 'Edit category' : 'New category'">
    <x-admin.page-header :title="$category->exists ? 'Edit category' : 'New category'">
        <a href="{{ route('admin.categories.index') }}" class="admin-btn-outline">Back</a>
    </x-admin.page-header>

    <form method="POST"
          action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="admin-card space-y-6 p-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <x-admin.translations label="Name" name="name" :values="$category->name ?? []" />
        <x-admin.translations label="Description (shown on the category page and in Google)" name="description" :values="$category->description ?? []" textarea :required="[]" />

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <span class="mb-1 block text-sm font-medium">Page address</span>
                @if ($category->exists)
                    <a href="{{ route('category', $category) }}" target="_blank" class="block truncate rounded-lg bg-black/5 px-3 py-2 font-mono text-sm hover:underline">/catalog/{{ $category->slug }}</a>
                @else
                    <span class="block rounded-lg bg-black/5 px-3 py-2 text-sm text-black/50">Generated from the English name on save</span>
                @endif
            </div>
            <x-admin.field label="Pack colour" name="color">
                <input type="color" name="color" value="{{ old('color', $category->color) }}" class="h-10 w-20 rounded border border-black/15">
            </x-admin.field>
            <x-admin.field label="Sort order" name="sort_order">
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="admin-input">
            </x-admin.field>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="accent-brand" @checked(old('is_active', $category->is_active))> Visible on the site and in the Telegram app
        </label>

        <button class="admin-btn">Save</button>
    </form>
</x-admin.layout>
