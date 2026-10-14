<x-admin.layout title="Sign in">
    <div class="mx-auto mt-16 max-w-sm">
        <h1 class="mb-6 text-center text-2xl font-bold">SAXIY admin</h1>
        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-card space-y-4 p-6">
            @csrf
            <x-admin.field label="Email" name="email">
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="admin-input">
            </x-admin.field>
            <x-admin.field label="Password" name="password">
                <input type="password" name="password" required autocomplete="current-password" class="admin-input">
            </x-admin.field>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="accent-brand"> Remember me
            </label>
            <button class="admin-btn w-full">Sign in</button>
        </form>
    </div>
</x-admin.layout>
