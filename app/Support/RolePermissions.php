<?php

namespace App\Support;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Which back-office pages each role may open, edited on the role pages (Administration → Roles & access).
 * The owner always has every page and clients never have any; the other roles start from sensible defaults.
 */
class RolePermissions
{
    private const string SETTING_KEY = 'role_permissions';

    private const string CACHE_KEY = 'role-permissions';

    /**
     * @var array<string, array<int, string>>|null
     */
    private ?array $stored = null;

    /**
     * @return array<int, AdminPermission>
     */
    public function for(UserRole $role): array
    {
        if ($role === UserRole::Owner) {
            return AdminPermission::cases();
        }

        if (! $role->isStaff()) {
            return [];
        }

        $values = $this->stored()[$role->value] ?? null;

        if ($values === null) {
            return self::defaults($role);
        }

        return array_values(array_filter(AdminPermission::cases(), fn (AdminPermission $permission): bool => in_array($permission->value, $values, true)));
    }

    public function allows(UserRole $role, AdminPermission $permission): bool
    {
        return in_array($permission, $this->for($role), true);
    }

    public function isEditable(UserRole $role): bool
    {
        return $role->isStaff() && $role !== UserRole::Owner;
    }

    /**
     * @param  array<int, AdminPermission>  $permissions
     */
    public function update(UserRole $role, array $permissions): void
    {
        $stored = $this->stored();
        $stored[$role->value] = array_values(array_map(fn (AdminPermission $permission): string => $permission->value, $permissions));

        Setting::query()->updateOrCreate(['key' => self::SETTING_KEY], ['value' => $stored]);
        Cache::forget(self::CACHE_KEY);
        $this->stored = $stored;
    }

    /**
     * @return array<int, AdminPermission>
     */
    public static function defaults(UserRole $role): array
    {
        return match ($role) {
            UserRole::Owner => AdminPermission::cases(),
            UserRole::SalesManager => [AdminPermission::Orders, AdminPermission::Customers, AdminPermission::Products, AdminPermission::Categories],
            UserRole::Warehouse => [AdminPermission::Orders, AdminPermission::Stock, AdminPermission::Receipts, AdminPermission::Corrections, AdminPermission::SalesInvoices, AdminPermission::StockMovements],
            UserRole::Accountant => [AdminPermission::Orders, AdminPermission::Customers, AdminPermission::Payments, AdminPermission::Debts, AdminPermission::Expenses, AdminPermission::Report],
            UserRole::Client => [],
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function stored(): array
    {
        return $this->stored ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => (array) (Setting::query()->where('key', self::SETTING_KEY)->value('value') ?? []),
        );
    }
}
