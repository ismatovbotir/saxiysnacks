<x-admin.layout :title="$user->exists ? 'Edit admin' : 'New admin'">
    <x-admin.page-header :title="$user->exists ? 'Edit admin' : 'New admin'">
        <a href="{{ route('admin.users.index') }}" class="admin-btn-outline">Back</a>
    </x-admin.page-header>

    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="admin-card max-w-xl space-y-5 p-6">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <x-admin.field label="Name *" name="name">
            <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="admin-input">
        </x-admin.field>
        <x-admin.field label="Email (login) *" name="email">
            <input type="email" name="email" required autocomplete="off" value="{{ old('email', $user->email) }}" class="admin-input">
        </x-admin.field>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.field :label="$user->exists ? 'New password' : 'Password *'" name="password" :hint="$user->exists ? 'Leave empty to keep the current password.' : 'At least 8 characters.'">
                <input type="password" name="password" autocomplete="new-password" @required(! $user->exists) class="admin-input">
            </x-admin.field>
            <x-admin.field label="Repeat password" name="password_confirmation">
                <input type="password" name="password_confirmation" autocomplete="new-password" class="admin-input">
            </x-admin.field>
        </div>

        <button class="admin-btn">Save</button>
    </form>
</x-admin.layout>
