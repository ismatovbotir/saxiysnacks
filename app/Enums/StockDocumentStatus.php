<?php

namespace App\Enums;

enum StockDocumentStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __(match ($this) {
            self::Draft => 'Draft',
            self::Posted => 'Posted',
            self::Cancelled => 'Cancelled',
        });
    }
}
