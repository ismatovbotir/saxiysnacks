<?php

namespace App\View\Composers;

use App\Enums\AdminPermission;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\StockDocument;
use App\Models\TelegramClient;
use Illuminate\Http\Request;
use Illuminate\View\View;
use WeakReference;

/**
 * Admin menu: the dashboard, then the pages grouped by business area (AdminPermission::group()).
 * Only pages the user's role may open are listed; groups without any such page disappear.
 */
class AdminNavigationComposer
{
    /**
     * Pages offered in the phone tab bar, in priority order; the first ones the role can open are used.
     *
     * @var array<int, AdminPermission>
     */
    private const array MOBILE_TAB_PRIORITY = [
        AdminPermission::Orders, AdminPermission::Customers, AdminPermission::Products,
        AdminPermission::Stock, AdminPermission::Payments, AdminPermission::Report,
    ];

    /**
     * Bottom tab bar on phones: the dashboard plus this many pages, then "More".
     */
    private const int MOBILE_TABS = 3;

    /**
     * Memoised per request, so the layout and the page header share one set of badge queries.
     *
     * @var array{request: WeakReference<Request>, data: array<string, mixed>}|null
     */
    private ?array $memo = null;

    public function compose(View $view): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($this->memo === null || $this->memo['request']->get() !== request()) {
            $this->memo = ['request' => WeakReference::create(request()), 'data' => $this->data()];
        }

        $view->with($this->memo['data']);
    }

    /**
     * @return array<string, mixed>
     */
    private function data(): array
    {
        $user = auth()->user();
        $allowed = collect(AdminPermission::cases())->filter(fn (AdminPermission $permission): bool => $user->canAccess($permission));

        $badges = [
            AdminPermission::Orders->value => $allowed->contains(AdminPermission::Orders) ? Order::query()->where('status', OrderStatus::New)->count() : 0,
            AdminPermission::Customers->value => $allowed->contains(AdminPermission::Customers) ? TelegramClient::query()->where('created_at', '>=', now()->subDay())->count() : 0,
        ];

        // The open page belongs to the most specific matching item, e.g. "admin.stock.documents.show" → Documents, not Stock.
        $pattern = fn (string $routeName): string => preg_replace('/\.(index|edit)$/', '.*', $routeName);
        $activeRoute = $allowed->map(fn (AdminPermission $permission): string => $permission->route())
            ->filter(fn (string $name): bool => request()->routeIs($name) || request()->routeIs($pattern($name)))
            ->sortByDesc(fn (string $name): int => (request()->routeIs($name) ? 1000 : 0) + strlen($pattern($name)))
            ->first();

        $openDocument = request()->route('document');

        if ($openDocument instanceof StockDocument) {
            $activeRoute = $openDocument->type->permission()->route();
        }

        $item = fn (string $routeName, string $label, string $icon, int $badge): array => [
            'url' => route($routeName),
            'label' => $label,
            'icon' => $icon,
            'badge' => $badge,
            'active' => $routeName === 'admin.dashboard' ? request()->routeIs($routeName) : $routeName === $activeRoute,
        ];

        $home = $item('admin.dashboard', __('Dashboard'), 'home', 0);
        $navigation = [];
        $current = $home['active'] ? ['group' => null, 'label' => $home['label'], 'url' => $home['url']] : null;

        foreach ($allowed as $permission) {
            $entry = [...$item($permission->route(), $permission->label(), $permission->icon(), $badges[$permission->value] ?? 0), 'permission' => $permission];
            $navigation[__($permission->group())][] = $entry;

            if ($entry['active']) {
                $current = ['group' => __($permission->group()), 'label' => $entry['label'], 'url' => $entry['url']];
            }
        }

        $mobileTabs = collect([$home])->concat(
            collect(self::MOBILE_TAB_PRIORITY)
                ->filter(fn (AdminPermission $permission): bool => $allowed->contains($permission))
                ->take(self::MOBILE_TABS)
                ->map(fn (AdminPermission $permission): array => collect($navigation[__($permission->group())])->firstWhere('permission', $permission)),
        )->values();

        return [
            'navigationHome' => $home,
            'navigation' => $navigation,
            'navigationCurrent' => $current,
            'navigationMobileTabs' => $mobileTabs,
            'navigationMoreActive' => $current !== null && ! $mobileTabs->contains('url', $current['url']),
            'navigationBadgeTotal' => array_sum($badges),
        ];
    }
}
