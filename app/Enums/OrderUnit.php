<?php

namespace App\Enums;

/**
 * What one unit of an order line means: consumers order single pieces, resellers order whole packages.
 */
enum OrderUnit: string
{
    case Piece = 'piece';
    case Package = 'package';

    /**
     * Short admin label, e.g. "pcs" / "pkg".
     */
    public function label(): string
    {
        return __(match ($this) {
            self::Piece => 'pcs',
            self::Package => 'pkg',
        });
    }

    /**
     * English label for the admin Telegram chat, which is not translated.
     */
    public function chatLabel(int $count): string
    {
        return match ($this) {
            self::Piece => 'pcs',
            self::Package => $count === 1 ? 'package' : 'packages',
        };
    }
}
