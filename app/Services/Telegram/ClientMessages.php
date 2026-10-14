<?php

namespace App\Services\Telegram;

use App\Models\Order;
use App\Models\TelegramClient;
use App\Support\Money;
use App\Support\SiteSettings;

/**
 * Localized messages the bot sends to customers in their private chat.
 */
class ClientMessages
{
    public function __construct(private SiteSettings $settings) {}

    public function welcome(?TelegramClient $client, string $locale): string
    {
        $key = match (true) {
            $client === null => 'bot.welcome_guest',
            ! $client->isActive() => 'bot.welcome_blocked',
            $client->isReseller() => 'bot.welcome_reseller',
            default => 'bot.welcome_customer',
        };

        return __($key, [
            'name' => e($client?->first_name),
            'phone' => e($this->settings->primaryPhone()),
        ], $locale);
    }

    public function registered(TelegramClient $client): string
    {
        return __('bot.registered', ['name' => e($client->first_name)], $client->language_code);
    }

    public function locationSaved(TelegramClient $client): string
    {
        return __('bot.location_saved', [], $client->language_code);
    }

    public function typeChanged(TelegramClient $client): string
    {
        $key = $client->isReseller() ? 'bot.became_reseller' : 'bot.became_consumer';

        return __($key, ['name' => e($client->first_name)], $client->language_code);
    }

    public function statusChanged(TelegramClient $client): string
    {
        $key = $client->isActive() ? 'bot.unblocked' : 'bot.blocked';

        return __($key, ['phone' => e($this->settings->primaryPhone())], $client->language_code);
    }

    public function orderReceived(Order $order, TelegramClient $client): string
    {
        return __('bot.order_received', [
            'number' => $order->number(),
            'total' => Money::format($order->total),
        ], $client->language_code);
    }

    public function orderStatusChanged(Order $order, TelegramClient $client): string
    {
        return __('bot.order_status_changed', [
            'number' => $order->number(),
            'status' => __('bot.order_status.'.$order->status->value, [], $client->language_code),
        ], $client->language_code);
    }

    /**
     * Inline keyboard with a button that launches the Mini App.
     *
     * @return array{inline_keyboard: array<int, array<int, array<string, mixed>>>}
     */
    public function catalogKeyboard(string $locale): array
    {
        return ['inline_keyboard' => [[
            ['text' => __('bot.open_catalog', [], $locale), 'web_app' => ['url' => route('webapp')]],
        ]]];
    }
}
