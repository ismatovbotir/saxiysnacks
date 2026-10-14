<?php

namespace App\View\Composers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\TelegramClient;
use Illuminate\Http\Request;
use Illuminate\View\View;
use WeakReference;

/**
 * Admin menu grouped by business area, with "needs attention" badges and the current page's section.
 */
class AdminNavigationComposer
{
    /**
     * Menu groups in display order: [group label => [route name => [label, icon, badge key|null, mobile tab?]]].
     * Labels are translation keys (lang/uz.json, lang/ru.json).
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: string|null, 3: bool}>>
     */
    public const array GROUPS = [
        'Sales' => [
            'admin.dashboard' => ['Dashboard', 'home', null, true],
            'admin.orders.index' => ['Orders', 'receipt', 'newOrders', true],
            'admin.clients.index' => ['Customers', 'users', 'newClients', true],
        ],
        'Catalog' => [
            'admin.products.index' => ['Products', 'box', null, true],
            'admin.categories.index' => ['Categories', 'tag', null, false],
        ],
        'Settings' => [
            'admin.settings.edit' => ['Contacts & social', 'globe', null, false],
            'admin.telegram.edit' => ['Telegram bot', 'send', null, false],
            'admin.users.index' => ['Admins', 'shield', null, false],
        ],
    ];

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
        $badges = [
            'newOrders' => Order::query()->where('status', OrderStatus::New)->count(),
            'newClients' => TelegramClient::query()->where('created_at', '>=', now()->subDay())->count(),
        ];

        $navigation = [];
        $current = null;

        foreach (self::GROUPS as $group => $items) {
            $group = __($group);

            foreach ($items as $routeName => [$label, $icon, $badgeKey, $mobileTab]) {
                $label = __($label);
                $active = request()->routeIs(preg_replace('/\.(index|edit)$/', '.*', $routeName));

                $navigation[$group][] = [
                    'url' => route($routeName),
                    'label' => $label,
                    'icon' => $icon,
                    'badge' => $badgeKey ? $badges[$badgeKey] : 0,
                    'active' => $active,
                    'mobileTab' => $mobileTab,
                ];

                if ($active) {
                    $current = ['group' => $group, 'label' => $label, 'url' => route($routeName)];
                }
            }
        }

        return [
            'navigation' => $navigation,
            'navigationCurrent' => $current,
            'navigationMoreActive' => $current !== null && ! collect($navigation[$current['group']])->firstWhere('label', $current['label'])['mobileTab'],
            'navigationBadgeTotal' => array_sum($badges),
        ];
    }
}
