<?php

namespace App\Models\Concerns;

/**
 * Reads values from JSON columns keyed by locale ("uz", "ru", "en").
 */
trait HasTranslations
{
    public const array LOCALES = ['uz', 'ru', 'en'];

    public function translate(string $attribute, ?string $locale = null): string
    {
        /** @var array<string, string|null> $values */
        $values = $this->getAttribute($attribute) ?? [];
        $locale ??= app()->getLocale();

        return $values[$locale]
            ?? $values['ru']
            ?? collect($values)->filter()->first()
            ?? '';
    }
}
