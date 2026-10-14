<?php

namespace App\Support;

class Money
{
    /**
     * Format an amount in Uzbek soum, e.g. "120 000 so'm".
     */
    public static function format(?int $amount): string
    {
        if ($amount === null) {
            return '—';
        }

        return number_format($amount, 0, '.', ' ')." so'm";
    }
}
