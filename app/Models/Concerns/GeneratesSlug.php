<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Assigns a unique, permanent URL slug when the model is first created.
 * Slugs never change afterwards so published URLs (and search rankings) stay valid.
 */
trait GeneratesSlug
{
    abstract protected function slugSource(): string;

    protected static function bootGeneratesSlug(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlug(Str::slug($model->slugSource()) ?: Str::lower(Str::random(8)));
            }
        });
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
