<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';

    public function label(): string
    {
        return __(match ($this) {
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::Transfer => 'Bank transfer',
        });
    }
}
