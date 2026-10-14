<?php

namespace App\Enums;

/**
 * Warehouse document kinds: a receipt (incoming invoice) adds stock, a sale (outgoing invoice) removes it,
 * and a correction adds or removes per line (surplus, shortage, write-off).
 */
enum StockDocumentType: string
{
    case Receipt = 'receipt';
    case Correction = 'correction';
    case Sale = 'sale';

    public function label(): string
    {
        return __(match ($this) {
            self::Receipt => 'Receipt invoice',
            self::Correction => 'Correction',
            self::Sale => 'Sales invoice',
        });
    }

    /**
     * Plural menu name, e.g. "Receipts".
     */
    public function pluralLabel(): string
    {
        return $this->permission()->label();
    }

    /**
     * URL segment of the type's list, e.g. /admin/stock/receipts.
     */
    public function slug(): string
    {
        return match ($this) {
            self::Receipt => 'receipts',
            self::Correction => 'corrections',
            self::Sale => 'sales',
        };
    }

    public function permission(): AdminPermission
    {
        return match ($this) {
            self::Receipt => AdminPermission::Receipts,
            self::Correction => AdminPermission::Corrections,
            self::Sale => AdminPermission::SalesInvoices,
        };
    }

    public static function fromSlug(string $slug): self
    {
        foreach (self::cases() as $type) {
            if ($type->slug() === $slug) {
                return $type;
            }
        }

        abort(404);
    }

    public function hint(): string
    {
        return __(match ($this) {
            self::Receipt => 'Goods arriving from production or a supplier.',
            self::Correction => 'Surplus or shortage after a count, damage, write-off. Use minus for less.',
            self::Sale => 'Goods leaving to a customer. Shipped orders create one automatically.',
        });
    }

    public function prefix(): string
    {
        return match ($this) {
            self::Receipt => 'IN',
            self::Correction => 'ADJ',
            self::Sale => 'OUT',
        };
    }

    /**
     * Sign applied to line quantities when posting; corrections keep each line's own sign.
     */
    public function direction(): int
    {
        return match ($this) {
            self::Receipt => 1,
            self::Correction => 0,
            self::Sale => -1,
        };
    }

    public function movementType(): StockMovementType
    {
        return match ($this) {
            self::Receipt => StockMovementType::Receipt,
            self::Correction => StockMovementType::Adjustment,
            self::Sale => StockMovementType::Shipment,
        };
    }

    public function counterpartyLabel(): string
    {
        return __(match ($this) {
            self::Receipt => 'Supplier',
            self::Correction => 'Reason',
            self::Sale => 'Customer',
        });
    }

    public function hasPrices(): bool
    {
        return $this !== self::Correction;
    }

    public function icon(): string
    {
        return $this->permission()->icon();
    }
}
