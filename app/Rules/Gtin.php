<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A GS1 barcode number (GTIN-8, -12, -13 or -14) with a valid check digit.
 *
 * @see https://www.gs1.org/services/how-calculate-check-digit-manually
 */
class Gtin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = (string) $value;

        if (! preg_match('/^(\d{8}|\d{12,14})$/', $digits)) {
            $fail(__('The GTIN must have 8, 12, 13 or 14 digits.'));

            return;
        }

        $sum = 0;

        foreach (str_split(strrev(substr($digits, 0, -1))) as $position => $digit) {
            $sum += (int) $digit * ($position % 2 === 0 ? 3 : 1);
        }

        if ((10 - $sum % 10) % 10 !== (int) substr($digits, -1)) {
            $fail(__('The GTIN check digit is wrong — please check the barcode number.'));
        }
    }
}
