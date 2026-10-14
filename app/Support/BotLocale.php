<?php

namespace App\Support;

class BotLocale
{
    public const array SUPPORTED = ['uz', 'ru', 'en'];

    /**
     * Map a Telegram language_code (e.g. "ru", "en-US", "kk") to a supported locale.
     */
    public static function fromTelegram(?string $languageCode): string
    {
        $language = strtolower(substr((string) $languageCode, 0, 2));

        return in_array($language, self::SUPPORTED, true) ? $language : 'ru';
    }
}
