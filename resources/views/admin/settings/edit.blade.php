<x-admin.layout :title="__('Contacts & social')">
    <x-admin.page-header :title="__('Contacts & social')" :subtitle="__('Shown on the website, in Google results and in the Telegram bot.')" />

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <section class="admin-card space-y-5 p-4 sm:p-6">
            <h2 class="font-semibold">{{ __('Company') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <x-admin.field :label="__('Brand name').' *'" name="brand_name">
                    <input type="text" name="brand_name" required value="{{ old('brand_name', $settings->get('brand_name')) }}" class="admin-input">
                </x-admin.field>
                <x-admin.field :label="__('Founded (year)')" name="founded_year">
                    <input type="number" name="founded_year" value="{{ old('founded_year', $settings->get('founded_year')) }}" class="admin-input">
                </x-admin.field>
            </div>
            <x-admin.translations :label="__('Legal name')" name="legal_name" :values="$settings->get('legal_name')" :required="[]" />
            <x-admin.translations :label="__('Short description (site footer and Google)')" name="tagline" :values="$settings->get('tagline')" textarea :required="[]" />
        </section>

        <section class="admin-card space-y-5 p-4 sm:p-6">
            <h2 class="font-semibold">{{ __('Contacts') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (range(0, 2) as $index)
                    <x-admin.field :label="__('Phone').' '.($index + 1).($index === 0 ? ' ('.__('main').')' : '')" :name="'phones.'.$index">
                        <input type="tel" name="phones[]" value="{{ old('phones.'.$index, $settings->get('phones')[$index] ?? '') }}" placeholder="+998 90 123-45-67" class="admin-input">
                    </x-admin.field>
                @endforeach
                <x-admin.field :label="__('Email')" name="email">
                    <input type="email" name="email" value="{{ old('email', $settings->get('email')) }}" class="admin-input">
                </x-admin.field>
                <x-admin.field :label="__('Sales manager\'s Telegram username')" name="telegram_contact" :hint="__('Inquiries from the website open a chat with this account. Empty = they go to the bot.')">
                    <div class="flex items-center rounded-lg border border-black/15 bg-white focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/30">
                        <span class="pl-3 text-sm text-black/50">t.me/</span>
                        <input type="text" name="telegram_contact" value="{{ old('telegram_contact', $settings->get('telegram_contact')) }}" placeholder="saxiy_sales" class="w-full rounded-lg bg-transparent px-1 py-2 text-sm outline-none">
                    </div>
                </x-admin.field>
                <x-admin.field :label="__('Map link (Google / Yandex Maps)')" name="map_url">
                    <input type="url" name="map_url" value="{{ old('map_url', $settings->get('map_url')) }}" placeholder="https://maps.app.goo.gl/…" class="admin-input">
                </x-admin.field>
            </div>
            <x-admin.translations :label="__('Address')" name="address" :values="$settings->get('address')" :required="[]" />
            <x-admin.translations :label="__('Working hours')" name="working_hours" :values="$settings->get('working_hours')" :required="[]" />
        </section>

        <section class="admin-card space-y-5 p-4 sm:p-6">
            <h2 class="font-semibold">{{ __('Social networks') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (\App\Support\SiteSettings::SOCIAL_NETWORKS as $network => $label)
                    <x-admin.field :label="$network === 'telegram' ? __('Telegram channel') : $label" :name="'social.'.$network">
                        <input type="url" name="social[{{ $network }}]" value="{{ old('social.'.$network, $settings->get('social.'.$network)) }}" placeholder="{{ ['telegram' => 'https://t.me/…', 'linkedin' => 'https://linkedin.com/company/…'][$network] ?? 'https://'.$network.'.com/…' }}" class="admin-input">
                    </x-admin.field>
                @endforeach
            </div>
        </section>

        <section class="admin-card space-y-4 p-4 sm:p-6">
            <h2 class="font-semibold">{{ __('Share preview image') }}</h2>
            <x-admin.field :label="__('Image shown when the site is shared or appears in search previews (1200×630 px recommended)')" name="og_image">
                @if ($settings->ogImageUrl())
                    <img src="{{ $settings->ogImageUrl() }}" alt="" class="mb-2 h-32 rounded-lg object-cover">
                @endif
                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp" class="admin-input">
            </x-admin.field>
        </section>

        <x-admin.form-actions :in-card="false" />
    </form>
</x-admin.layout>
