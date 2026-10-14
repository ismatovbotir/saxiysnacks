<?php

namespace App\Enums;

/**
 * Decides which product price a client sees and is charged.
 */
enum ClientType: string
{
    case Consumer = 'consumer';
    case Reseller = 'reseller';

    public function label(): string
    {
        return match ($this) {
            self::Consumer => 'Consumer',
            self::Reseller => 'Reseller',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Consumer => '👤',
            self::Reseller => '⭐',
        };
    }
}
