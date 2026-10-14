<?php

namespace App\Enums;

/**
 * One back-office page (menu item) a role may open. The order of the cases is the menu order;
 * group() is the menu group the page appears under.
 */
enum AdminPermission: string
{
    case Orders = 'orders';
    case Customers = 'customers';
    case Products = 'products';
    case Categories = 'categories';
    case Stock = 'stock';
    case Receipts = 'stock_receipts';
    case Corrections = 'stock_corrections';
    case SalesInvoices = 'stock_sales';
    case StockMovements = 'stock_movements';
    case Payments = 'payments';
    case Debts = 'debts';
    case Expenses = 'expenses';
    case Report = 'report';
    case Staff = 'staff';
    case Roles = 'roles';
    case Contacts = 'contacts';
    case Telegram = 'telegram';

    /**
     * Menu group as a translation key.
     */
    public function group(): string
    {
        return match ($this) {
            self::Orders, self::Customers => 'Sales',
            self::Products, self::Categories => 'Catalog',
            self::Stock, self::Receipts, self::Corrections, self::SalesInvoices, self::StockMovements => 'Warehouse',
            self::Payments, self::Debts, self::Expenses, self::Report => 'Finance',
            self::Staff, self::Roles, self::Contacts, self::Telegram => 'Administration',
        };
    }

    public function label(): string
    {
        return __(match ($this) {
            self::Orders => 'Orders',
            self::Customers => 'Customers',
            self::Products => 'Products',
            self::Categories => 'Categories',
            self::Stock => 'Stock',
            self::Receipts => 'Receipts',
            self::Corrections => 'Corrections',
            self::SalesInvoices => 'Sales invoices',
            self::StockMovements => 'Stock movements',
            self::Payments => 'Payments',
            self::Debts => 'Debts',
            self::Expenses => 'Expenses',
            self::Report => 'Report',
            self::Staff => 'Staff',
            self::Roles => 'Roles & access',
            self::Contacts => 'Contacts & social',
            self::Telegram => 'Telegram bot',
        });
    }

    /**
     * What the permission allows, shown on the role page.
     */
    public function description(): string
    {
        return __(match ($this) {
            self::Orders => 'See orders and change their status.',
            self::Customers => 'See customers, make resellers, block.',
            self::Products => 'Create and edit products, units and prices.',
            self::Categories => 'Create and edit categories.',
            self::Stock => 'See stock balances.',
            self::Receipts => 'Create, post and unpost receipt invoices.',
            self::Corrections => 'Create, post and unpost corrections (surplus, shortage, write-off).',
            self::SalesInvoices => 'Create, post and unpost sales invoices.',
            self::StockMovements => 'See the stock journal.',
            self::Payments => 'Record and delete payments.',
            self::Debts => 'See what customers owe.',
            self::Expenses => 'Add and delete expenses.',
            self::Report => 'See the finance report.',
            self::Staff => 'Add, edit and remove staff accounts.',
            self::Roles => 'Change what each role can open.',
            self::Contacts => 'Edit company contacts and social links.',
            self::Telegram => 'Connect the bot and the admin chat.',
        });
    }

    public function icon(): string
    {
        return match ($this) {
            self::Orders => 'receipt',
            self::Customers => 'users',
            self::Products => 'box',
            self::Categories => 'tag',
            self::Stock => 'warehouse',
            self::Receipts => 'download',
            self::Corrections => 'scale',
            self::SalesInvoices => 'upload',
            self::StockMovements => 'list',
            self::Payments => 'wallet',
            self::Debts => 'alert',
            self::Expenses => 'minus',
            self::Report => 'chart',
            self::Staff => 'shield',
            self::Roles => 'key',
            self::Contacts => 'globe',
            self::Telegram => 'send',
        };
    }

    /**
     * Route name of the page's menu link.
     */
    public function route(): string
    {
        return match ($this) {
            self::Orders => 'admin.orders.index',
            self::Customers => 'admin.clients.index',
            self::Products => 'admin.products.index',
            self::Categories => 'admin.categories.index',
            self::Stock => 'admin.stock.index',
            self::Receipts => 'admin.stock.receipts.index',
            self::Corrections => 'admin.stock.corrections.index',
            self::SalesInvoices => 'admin.stock.sales.index',
            self::StockMovements => 'admin.stock.movements',
            self::Payments => 'admin.finance.payments.index',
            self::Debts => 'admin.finance.debts',
            self::Expenses => 'admin.finance.expenses.index',
            self::Report => 'admin.finance.report',
            self::Staff => 'admin.users.index',
            self::Roles => 'admin.roles.index',
            self::Contacts => 'admin.settings.edit',
            self::Telegram => 'admin.telegram.edit',
        };
    }

    /**
     * @return array<string, array<int, self>> permissions keyed by menu group, in menu order
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }
}
