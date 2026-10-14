<?php

namespace App\Enums;

/**
 * Built-in SVG pack illustrations used when a product has no uploaded photo.
 * The value is "{art}:{variant}" as understood by the storefront pack() renderer.
 */
enum ProductIllustration: string
{
    case KurtBalls = 'ball:plain';
    case KurtBallsMix = 'ball:mix';
    case KurtBallsBerry = 'ball:berry';
    case KurtBars = 'square:plain';
    case KurtSingle = 'ball1:plain';
    case KurtStick = 'stick1:plain';
    case Peanut = 'nut:peanut';
    case Cashew = 'nut:cashew';
    case Almond = 'nut:almond';
    case Pistachio = 'nut:pista';
    case ApricotKernels = 'nut:pit';
    case CheeseSticks = 'stick:sul';

    public function label(): string
    {
        return match ($this) {
            self::KurtBalls => 'Kurt balls',
            self::KurtBallsMix => 'Kurt balls, assorted',
            self::KurtBallsBerry => 'Kurt balls, berry',
            self::KurtBars => 'Kurt bars',
            self::KurtSingle => 'Single kurt ball',
            self::KurtStick => 'Kurt stick (Tosh)',
            self::Peanut => 'Nuts: peanut',
            self::Cashew => 'Nuts: cashew',
            self::Almond => 'Nuts: almond',
            self::Pistachio => 'Nuts: pistachio',
            self::ApricotKernels => 'Nuts: apricot kernels',
            self::CheeseSticks => 'Cheese sticks (suluguni)',
        };
    }
}
