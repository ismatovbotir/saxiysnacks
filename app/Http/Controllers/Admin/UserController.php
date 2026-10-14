<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::query()->where('role', '!=', UserRole::Client)->orderBy('name')->get()]);
    }

    public function create(Request $request): View
    {
        $role = UserRole::tryFrom($request->string('role')->value());

        return view('admin.users.form', ['user' => new User(['role' => $role?->isStaff() ? $role : UserRole::SalesManager])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->safe()->except('unlink_telegram'));

        return redirect()->route('admin.users.index')->with('status', __('Staff member created.'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->role === UserRole::Owner && $request->enum('role', UserRole::class) !== UserRole::Owner && $this->isLastOwner($user)) {
            return back()->withInput()->with('error', __('Keep at least one owner: make someone else an owner first.'));
        }

        $attributes = $request->safe()->except(['unlink_telegram', 'password']);

        if ($request->filled('password')) {
            $attributes['password'] = $request->validated('password');
        }

        $user->fill($attributes);

        // A linked chat proved the old number; a new number has to be shared in the bot again.
        if ($request->boolean('unlink_telegram') || $user->isDirty('phone')) {
            $user->telegram_chat_id = null;
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', __('Staff member updated.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        if ($user->role === UserRole::Owner && $this->isLastOwner($user)) {
            return back()->with('error', __('Keep at least one owner: make someone else an owner first.'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('Staff member deleted.'));
    }

    private function isLastOwner(User $user): bool
    {
        return ! User::query()->where('role', UserRole::Owner)->whereKeyNot($user->getKey())->exists();
    }
}
