<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Blocked => 'Blocked',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Active => '✅',
            self::Blocked => '⛔',
        };
    }
}
