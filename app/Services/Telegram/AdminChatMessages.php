<?php

namespace App\Services\Telegram;

use App\Enums\OrderStatus;
use App\Models\Order;
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

    public function orderText(Order $order): string
    {
        $order->loadMissing(['client', 'items']);

        $lines = $order->items->map(fn ($item): string => sprintf(
            '• %s, %d g — %d × %s = %s',
            e($item->product_name),
            $item->weight_grams,
            $item->quantity,
            Money::format($item->unit_price),
            Money::format($item->line_total),
        ));

        return implode("\n", array_filter([
            "<b>🧾 Order {$order->number()}</b>",
            '',
            '<b>Customer:</b> '.e($order->client->displayName()).($order->client->vat_number ? ' (VAT '.e($order->client->vat_number).')' : ''),
            '<b>Contact:</b> '.e($order->client->fullName()).', '.e($order->client->phone),
            '<b>Prices:</b> '.$order->price_type->emoji().' '.$order->price_type->label(),
            '<b>Delivery:</b> '.$this->mapLinks($order->delivery_latitude, $order->delivery_longitude, $order->delivery_address),
            '',
            $lines->implode("\n"),
            '',
            '<b>Total:</b> '.Money::format($order->total),
            $order->comment ? '<b>Comment:</b> '.e(Str::limit($order->comment, 500)) : null,
            '',
            '<b>Status:</b> '.$order->status->emoji().' '.$order->status->label(),
        ], fn (?string $line): bool => $line !== null));
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
