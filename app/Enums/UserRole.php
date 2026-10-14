<?php

namespace App\Enums;

/**
 * Who a user account belongs to. Staff roles open the back-office pages set on their role page
 * (see RolePermissions); clients never do (their accounts are reserved for the future customer cabinet).
 */
enum UserRole: string
{
    case Owner = 'owner';
    case SalesManager = 'sales_manager';
    case Warehouse = 'warehouse';
    case Accountant = 'accountant';
    case Client = 'client';

    public function description(): string
    {
        return __(match ($this) {
            self::Owner => 'Runs the business: every page, always.',
            self::SalesManager => 'Takes orders, calls customers back, keeps the catalog up to date.',
            self::Warehouse => 'Receives goods, keeps stock right, ships orders.',
            self::Accountant => 'Records payments and expenses, follows debts and reports.',
            self::Client => 'Customer account for the future cabinet; no back-office access.',
        });
    }

    public function label(): string
    {
        return __(match ($this) {
            self::Owner => 'Owner',
            self::SalesManager => 'Sales manager',
            self::Warehouse => 'Warehouse',
            self::Accountant => 'Accountant',
            self::Client => 'Client',
        });
    }

    public function isStaff(): bool
    {
        return $this !== self::Client;
    }

    /**
     * @return array<int, self>
     */
    public static function staff(): array
    {
        return array_values(array_filter(self::cases(), fn (self $role): bool => $role->isStaff()));
    }
}
