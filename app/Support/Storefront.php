<?php

namespace App\Support;

/**
 * URL helpers for the localized public site: Uzbek lives at the root, Russian and English under /ru and /en.
 */
class Storefront
{
    public const string DEFAULT_LOCALE = 'uz';

    public const array LOCALES = ['uz', 'ru', 'en'];

    /**
     * @param  array<string, mixed>|mixed  $parameters
     */
    public static function route(string $name, mixed $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if ($locale === self::DEFAULT_LOCALE) {
            return route($name, $parameters);
        }

        return route("localized.{$name}", ['locale' => $locale, ...$parameters]);
    }

    /**
     * URLs of the same page in every language, keyed by locale.
     *
     * @param  array<string, mixed>|mixed  $parameters
     * @return array<string, string>
     */
    public static function alternates(string $name, mixed $parameters = []): array
    {
        return collect(self::LOCALES)
            ->mapWithKeys(fn (string $locale): array => [$locale => self::route($name, $parameters, $locale)])
            ->all();
    }

    public static function ogLocale(string $locale): string
    {
        return match ($locale) {
            'uz' => 'uz_UZ',
            'ru' => 'ru_RU',
            default => 'en_US',
        };
    }
}
