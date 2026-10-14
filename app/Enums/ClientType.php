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
        return __(match ($this) {
            self::Consumer => 'Consumer',
            self::Reseller => 'Reseller',
        });
    }

    /**
     * Consumers order by the piece, resellers by the package.
     */
    public function orderUnit(): OrderUnit
    {
        return match ($this) {
            self::Consumer => OrderUnit::Piece,
            self::Reseller => OrderUnit::Package,
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
