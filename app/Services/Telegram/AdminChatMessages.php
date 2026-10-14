<?php

namespace App\Services\Telegram;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TelegramClient;
use App\Support\Maps;
use App\Support\Money;
use Illuminate\Support\Str;

/**
 * Builds the messages and callback buttons posted to the admins' Telegram chat.
 *
 * Callback data has the form "{entity}:{action}:{uuid}".
 */
class AdminChatMessages
{
    private const string TIMEZONE = 'Asia/Tashkent';

    /**
     * Telegram allows 4096 characters per message after entities are parsed; keep some headroom.
     */
    private const int MESSAGE_LIMIT = 3900;

    public function clientText(TelegramClient $client): string
    {
        $username = $client->telegram_username ? ' (@'.e($client->telegram_username).')' : '';

        return implode("\n", array_filter([
            '<b>👤 New customer</b>',
            '',
            '<b>Name:</b> '.e($client->fullName()).$username,
            '<b>Phone:</b> '.e($client->phone),
            $client->company_name ? '<b>Company:</b> '.e($client->company_name) : null,
            $client->vat_number ? '<b>VAT number:</b> '.e($client->vat_number) : null,
            $client->hasLocation() ? '<b>Location:</b> '.$this->mapLinks($client->latitude, $client->longitude, $client->address) : null,
            '',
            '<b>Type:</b> '.$client->type->emoji().' '.$client->type->label().' prices',
            '<b>Status:</b> '.$client->status->emoji().' '.$client->status->label(),
        ], fn (?string $line): bool => $line !== null));
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array<string, string>>>}
     */
    public function clientKeyboard(TelegramClient $client): array
    {
        $row = [
            $client->isReseller()
                ? $this->callbackButton('👤 Make consumer', "client:consumer:{$client->uuid}")
                : $this->callbackButton('⭐ Make reseller', "client:reseller:{$client->uuid}"),
            $client->isActive()
                ? $this->callbackButton('⛔ Block', "client:block:{$client->uuid}")
                : $this->callbackButton('✅ Unblock', "client:unblock:{$client->uuid}"),
        ];

        return ['inline_keyboard' => $this->withAdminLink([$row], route('admin.clients.show', $client))];
    }

    /**
     * Full order sheet for the admin chat: customer card, delivery, every line with boxes and pieces, and totals.
     * Long orders are shortened to fit Telegram's message limit; the admin panel link shows the rest.
     */
    public function orderText(Order $order): string
    {
        $order->loadMissing(['client', 'items.product']);
        $client = $order->client;

        $previousOrders = $client->orders()->whereKeyNot($order->getKey())->count();
        $boxes = $order->items->sum('quantity');
        $pieces = $order->items->sum(fn (OrderItem $item): int => $item->quantity * ($item->box_quantity ?? 0));
        $kilograms = $order->items->sum(fn (OrderItem $item): int => $item->quantity * ($item->box_quantity ?? 0) * $item->weight_grams) / 1000;

        $items = $order->items->values()->map(fn (OrderItem $item, int $index): string => implode("\n", array_filter([
            ($index + 1).'. <b>'.e($item->product_name).'</b>, '.$item->weight_grams.' g'.($item->product?->sku ? ' · <code>'.e($item->product->sku).'</code>' : '')
                .($item->product?->class_code ? ' · MXIK <code>'.e($item->product->class_code).'</code>' : ''),
            '    '.$item->quantity.' box'.($item->quantity === 1 ? '' : 'es')
                .($item->box_quantity ? ' × '.$item->box_quantity.' pcs = '.number_format($item->quantity * $item->box_quantity, 0, '.', ' ').' pcs' : ''),
            '    '.Money::format($item->unit_price).' / box → <b>'.Money::format($item->line_total).'</b>',
        ])));

        $header = [
            "<b>🧾 Order {$order->number()}</b>",
            '🕐 '.$order->created_at->timezone(self::TIMEZONE)->format('d.m.Y H:i').' (Tashkent)',
            '',
            '<b>👤 Customer</b>',
            '<a href="tg://user?id='.$client->telegram_id.'">'.e($client->fullName()).'</a>'.($client->telegram_username ? ' (@'.e($client->telegram_username).')' : ''),
            '📞 '.e($client->phone),
            $client->company_name ? '🏢 '.e($client->company_name).($client->vat_number ? ' · VAT '.e($client->vat_number) : '') : null,
            $client->company_name === null && $client->vat_number ? '🏢 VAT '.e($client->vat_number) : null,
            $order->price_type->emoji().' '.$order->price_type->label().' prices · '.($previousOrders === 0 ? '🆕 first order' : "🔁 {$previousOrders} earlier order".($previousOrders === 1 ? '' : 's')),
            '',
            '<b>📍 Delivery</b>',
            $order->delivery_address ? e($order->delivery_address) : null,
            $this->mapLinks($order->delivery_latitude, $order->delivery_longitude, null),
            '',
            '<b>📦 Items</b> ('.$order->items->count().' product'.($order->items->count() === 1 ? '' : 's').')',
        ];

        $footer = [
            '',
            '<b>Boxes:</b> '.$boxes.($pieces > 0 ? ' · <b>Pieces:</b> '.number_format($pieces, 0, '.', ' ') : '').($kilograms > 0 ? ' · <b>Weight:</b> ≈'.rtrim(rtrim(number_format($kilograms, 2, '.', ''), '0'), '.').' kg' : ''),
            '<b>💰 Total: '.Money::format($order->total).'</b>',
            $order->comment ? "\n<b>💬 Comment:</b>\n".e(Str::limit($order->comment, 800)) : null,
            '',
            '<b>Status:</b> '.$order->status->emoji().' '.$order->status->label(),
        ];

        $shown = $items->count();

        do {
            $body = $items->take($shown)->implode("\n");

            if ($shown < $items->count()) {
                $body .= "\n… and ".($items->count() - $shown).' more — see the admin panel';
            }

            $text = $this->lines([...$header, $body, ...$footer]);
        } while (mb_strlen(strip_tags($text)) > self::MESSAGE_LIMIT && --$shown > 0);

        return $text;
    }

    /**
     * @param  array<int, string|null>  $lines
     */
    private function lines(array $lines): string
    {
        return implode("\n", array_filter($lines, fn (?string $line): bool => $line !== null));
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array<string, string>>>}
     */
    public function orderKeyboard(Order $order): array
    {
        $rows = collect($this->nextOrderStatuses($order->status))
            ->map(fn (OrderStatus $status): array => $this->callbackButton(
                $status->emoji().' '.$status->label(),
                "order:{$status->value}:{$order->uuid}",
            ))
            ->chunk(2)
            ->map(fn ($chunk): array => $chunk->values()->all())
            ->values()
            ->all();

        return ['inline_keyboard' => $this->withAdminLink($rows, route('admin.orders.show', $order))];
    }

    /**
     * @return array<int, OrderStatus>
     */
    public function nextOrderStatuses(OrderStatus $status): array
    {
        return match ($status) {
            OrderStatus::New => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Shipped => [OrderStatus::Completed],
            OrderStatus::Completed, OrderStatus::Cancelled => [],
        };
    }

    private function mapLinks(float $latitude, float $longitude, ?string $address): string
    {
        return ($address ? e($address).' · ' : '')
            .'<a href="'.e(Maps::google($latitude, $longitude)).'">Google</a> · '
            .'<a href="'.e(Maps::yandex($latitude, $longitude)).'">Yandex</a>';
    }

    /**
     * @return array{text: string, callback_data: string}
     */
    private function callbackButton(string $text, string $data): array
    {
        return ['text' => $text, 'callback_data' => $data];
    }

    /**
     * Telegram rejects URL buttons pointing to non-public hosts, so the link is only added over HTTPS.
     *
     * @param  array<int, array<int, array<string, string>>>  $rows
     * @return array<int, array<int, array<string, string>>>
     */
    private function withAdminLink(array $rows, string $url): array
    {
        if (str_starts_with($url, 'https://')) {
            $rows[] = [['text' => '🔗 Open in admin panel', 'url' => $url]];
        }

        return $rows;
    }
}
