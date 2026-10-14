<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RolePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Administration → Roles & access: every role on its own page, with the pages it may open and its staff.
 */
class RoleController extends Controller
{
    public function __construct(private RolePermissions $permissions) {}

    public function index(): View
    {
        $counts = User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.roles.index', [
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role): array => [
                'role' => $role,
                'users' => (int) ($counts[$role->value] ?? 0),
                'permissions' => $this->permissions->for($role),
            ]),
        ]);
    }

    public function show(UserRole $role): View
    {
        return view('admin.roles.show', [
            'role' => $role,
            'granted' => $this->permissions->for($role),
            'editable' => $this->permissions->isEditable($role),
            'users' => User::query()->where('role', $role)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, UserRole $role): RedirectResponse
    {
        abort_unless($this->permissions->isEditable($role), 403);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(AdminPermission::class)],
        ]);

        $this->permissions->update($role, array_map(fn (string $value): AdminPermission => AdminPermission::from($value), $validated['permissions'] ?? []));

        return redirect()->route('admin.roles.show', $role)->with('status', __('Access for :role saved.', ['role' => $role->label()]));
    }
}
