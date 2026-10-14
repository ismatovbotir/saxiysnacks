<?php

namespace App\Support;

/**
 * Phone numbers are stored as digits with the country code (998901234567) so typed and Telegram-shared numbers match.
 */
class Phone
{
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 9) {
            $digits = '998'.$digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /**
     * "+998 90 123 45 67" for Uzbek numbers, "+<digits>" otherwise.
     */
    public static function format(?string $phone): string
    {
        $digits = self::normalize($phone);

        if ($digits === null) {
            return (string) $phone;
        }

        return preg_match('/^998(\d{2})(\d{3})(\d{2})(\d{2})$/', $digits, $parts)
            ? "+998 {$parts[1]} {$parts[2]} {$parts[3]} {$parts[4]}"
            : '+'.$digits;
    }
}
