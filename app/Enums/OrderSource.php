<?php

namespace App\Enums;

/**
 * Where an order was placed: the Telegram Mini App (registered customers) or the website cart (guests who leave a phone).
 */
enum OrderSource: string
{
    case Telegram = 'telegram';
    case Website = 'website';

    public function label(): string
    {
        return __(match ($this) {
            self::Telegram => 'Telegram',
            self::Website => 'Website',
        });
    }
}
