<x-admin.layout :title="__('Sign in')">
    <div class="mb-6 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-brand text-xl font-bold text-ink">S</span>
        <h1 class="mt-3 text-2xl font-bold">SAXIY CRM</h1>
        <p class="text-sm text-black/60">{{ __('Orders, customers and catalog') }}</p>
    </div>
    <form method="POST" action="{{ route('admin.login.store') }}" class="admin-card space-y-4 p-5 sm:p-6">
        @csrf
        <x-admin.field :label="__('Email')" name="email">
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="admin-input py-2.5 text-base sm:text-sm">
        </x-admin.field>
        <x-admin.field :label="__('Password')" name="password">
            <input type="password" name="password" required autocomplete="current-password" class="admin-input py-2.5 text-base sm:text-sm">
        </x-admin.field>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="accent-brand"> {{ __('Remember me') }}
        </label>
        <button class="admin-btn w-full py-2.5">{{ __('Sign in') }}</button>
    </form>
</x-admin.layout>
