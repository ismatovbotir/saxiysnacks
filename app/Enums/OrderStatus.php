<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Normal next steps: new → confirmed → shipped → completed; only unshipped orders can be cancelled.
     * Anything else (e.g. undoing a shipment) is an owner override.
     *
     * @return array<int, self>
     */
    public function next(): array
    {
        return match ($this) {
            self::New => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canBecome(self $status): bool
    {
        return in_array($status, $this->next(), true);
    }

    public function label(): string
    {
        return __(match ($this) {
            self::New => 'New',
            self::Confirmed => 'Confirmed',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        });
    }

    public function emoji(): string
    {
        return match ($this) {
            self::New => '🆕',
            self::Confirmed => '✅',
            self::Shipped => '🚚',
            self::Completed => '📦',
            self::Cancelled => '⛔',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
