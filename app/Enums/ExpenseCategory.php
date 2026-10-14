<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case RawMaterials = 'raw_materials';
    case Packaging = 'packaging';
    case Salaries = 'salaries';
    case Logistics = 'logistics';
    case Rent = 'rent';
    case Utilities = 'utilities';
    case Marketing = 'marketing';
    case Taxes = 'taxes';
    case Other = 'other';

    public function label(): string
    {
        return __(match ($this) {
            self::RawMaterials => 'Raw materials',
            self::Packaging => 'Packaging',
            self::Salaries => 'Salaries',
            self::Logistics => 'Logistics',
            self::Rent => 'Rent',
            self::Utilities => 'Utilities',
            self::Marketing => 'Marketing',
            self::Taxes => 'Taxes & fees',
            self::Other => 'Other',
        });
    }
}
