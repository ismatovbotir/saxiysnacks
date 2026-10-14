<?php

namespace App\Enums;

/**
 * Why stock changed. Receipts, write-offs and adjustments are entered by staff; shipments and returns
 * are recorded automatically when an order is shipped or a shipped order is cancelled.
 */
enum StockMovementType: string
{
    case Receipt = 'receipt';
    case WriteOff = 'write_off';
    case Adjustment = 'adjustment';
    case Shipment = 'shipment';
    case Return = 'return';

    public function label(): string
    {
        return __(match ($this) {
            self::Receipt => 'Receipt',
            self::WriteOff => 'Write-off',
            self::Adjustment => 'Adjustment',
            self::Shipment => 'Shipment',
            self::Return => 'Return',
        });
    }

    /**
     * The sign applied to the entered quantity; adjustments keep the sign staff type (e.g. -3 after a count).
     */
    public function direction(): int
    {
        return match ($this) {
            self::Receipt, self::Return => 1,
            self::WriteOff, self::Shipment => -1,
            self::Adjustment => 0,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function manual(): array
    {
        return [self::Receipt, self::WriteOff, self::Adjustment];
    }
}
