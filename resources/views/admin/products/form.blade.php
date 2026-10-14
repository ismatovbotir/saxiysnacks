<x-admin.layout :title="$product->exists ? 'Edit product' : 'New product'">
    <x-admin.page-header :title="$product->exists ? 'Edit product' : 'New product'">
        <a href="{{ route('admin.products.index') }}" class="admin-btn-outline">Back</a>
    </x-admin.page-header>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          class="admin-card space-y-6 p-6">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-3">
            <x-admin.field label="Category *" name="category_id">
                <select name="category_id" required class="admin-input">
                    <option value="">Choose…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->translate('name', 'ru') }}</option>
                    @endforeach
                </select>
            </x-admin.field>
            <div>
                <span class="mb-1 block text-sm font-medium">Page address</span>
                @if ($product->exists)
                    <a href="{{ route('product', [$product->category, $product]) }}" target="_blank" class="block truncate rounded-lg bg-black/5 px-3 py-2 font-mono text-sm hover:underline">…/{{ $product->slug }}</a>
                @else
                    <span class="block rounded-lg bg-black/5 px-3 py-2 text-sm text-black/50">Generated from the English name and weight on save</span>
                @endif
            </div>
            <x-admin.field label="SKU / barcode" name="sku">
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="admin-input">
            </x-admin.field>
        </div>

        <x-admin.translations label="Name" name="name" :values="$product->name ?? []" />
        <x-admin.translations label="Description (composition, shelf life — shown on the product page and in Google)" name="description" :values="$product->description ?? []" textarea :required="[]" />

        <div class="grid gap-4 md:grid-cols-3">
            <x-admin.field label="Weight, g *" name="weight_grams">
                <input type="number" name="weight_grams" min="1" required value="{{ old('weight_grams', $product->weight_grams) }}" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Pieces per box" name="box_quantity">
                <input type="number" name="box_quantity" min="1" value="{{ old('box_quantity', $product->box_quantity) }}" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Consumer price per box, so'm" name="consumer_price" hint="Default price for every registered customer. Empty = cannot be ordered.">
                <input type="text" inputmode="numeric" name="consumer_price" value="{{ old('consumer_price', $product->consumer_price) }}" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Reseller price per box, so'm" name="reseller_price" hint="Seen by customers marked as resellers. Empty = they pay the consumer price.">
                <input type="text" inputmode="numeric" name="reseller_price" value="{{ old('reseller_price', $product->reseller_price) }}" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Sort order" name="sort_order">
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="admin-input">
            </x-admin.field>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <x-admin.field label="Photo (JPG, PNG, WebP up to 4 MB)" name="image" hint="Recommended: square, at least 1200×1200 px — it is also used as the preview image in Google and messengers.">
                @if ($product->image_url)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ $product->image_url }}" alt="" class="size-20 rounded-lg object-cover">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1"> Remove photo</label>
                    </div>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Pack illustration (used when there is no photo)" name="illustration">
                <select name="illustration" class="admin-input">
                    <option value="">None</option>
                    @foreach (\App\Enums\ProductIllustration::cases() as $illustration)
                        <option value="{{ $illustration->value }}" @selected(old('illustration', $product->illustration?->value) === $illustration->value)>{{ $illustration->label() }}</option>
                    @endforeach
                </select>
            </x-admin.field>
        </div>

        <div class="flex flex-wrap gap-6 text-sm">
            @foreach (['is_active' => 'Visible on the site and in the Telegram app', 'is_featured' => 'Best seller (shown on the home page)', 'is_new' => 'Mark as new'] as $flag => $label)
                <label class="flex items-center gap-2">
                    <input type="hidden" name="{{ $flag }}" value="0">
                    <input type="checkbox" name="{{ $flag }}" value="1" class="accent-brand" @checked(old($flag, $product->{$flag}))> {{ $label }}
                </label>
            @endforeach
        </div>

        <button class="admin-btn">Save</button>
    </form>
</x-admin.layout>
