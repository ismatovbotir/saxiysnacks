<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Company contacts and social links edited in the admin panel ("Contacts & social").
 */
class SiteSettings
{
    private const string CACHE_KEY = 'site-settings';

    /**
     * Supported social profiles in display order, keyed by setting name.
     *
     * @var array<string, string>
     */
    public const array SOCIAL_NETWORKS = [
        'telegram' => 'Telegram',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
    ];

    /**
     * Defaults used until an admin saves the form.
     *
     * @var array<string, mixed>
     */
    public const array DEFAULTS = [
        'brand_name' => 'SAXIY',
        'legal_name' => ['uz' => '«SAXIY OMAD TRADING» QK', 'ru' => 'СП «SAXIY OMAD TRADING»', 'en' => 'JV "SAXIY OMAD TRADING"'],
        'tagline' => [
            'uz' => "Sut, yong'oq va pishloqdan premium sneklar. Toshkent viloyatida ishlab chiqariladi.",
            'ru' => 'Премиальные снеки из молока, орехов и сыра. Производство в Ташкентской области.',
            'en' => 'Premium snacks from milk, nuts and cheese. Made in the Tashkent region.',
        ],
        'address' => [
            'uz' => "Toshkent viloyati, Toshkent tumani, Pillakor ko'chasi, 55-uy",
            'ru' => 'Ташкентский район, Ташкентский регион, ул. Пиллакор, д. 55',
            'en' => '55 Pillakor St, Tashkent District, Tashkent Region',
        ],
        'working_hours' => ['uz' => 'Du–Sha, 9:00–18:00', 'ru' => 'Пн–Сб, 9:00–18:00', 'en' => 'Mon–Sat, 9:00–18:00'],
        'phones' => ['+998 94 694-44-99', '+998 99 177-44-99'],
        'email' => null,
        'telegram_contact' => null,
        'map_url' => null,
        'founded_year' => 2017,
        'social' => [],
        'og_image_path' => null,
    ];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolved = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->resolved ??= array_replace(
            self::DEFAULTS,
            Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()->pluck('value', 'key')->all()),
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    public function text(string $key, ?string $locale = null): string
    {
        $values = (array) $this->get($key, []);

        return $values[$locale ?? app()->getLocale()] ?? $values['ru'] ?? '';
    }

    public function primaryPhone(): ?string
    {
        return $this->get('phones')[0] ?? null;
    }

    /**
     * Chat link to the sales manager's Telegram account (falls back to the bot), optionally with a pre-filled message.
     *
     * @see https://core.telegram.org/api/links#public-username-links
     */
    public function telegramChatUrl(?string $text = null): ?string
    {
        $url = filled($this->get('telegram_contact'))
            ? 'https://t.me/'.$this->get('telegram_contact')
            : $this->telegramBotUrl();

        return $url && $text ? $url.'?text='.rawurlencode($text) : $url;
    }

    public function telegramBotUrl(): ?string
    {
        $username = ltrim((string) config('services.telegram.bot_username'), '@');

        return $username !== '' ? "https://t.me/{$username}" : null;
    }

    public function ogImageUrl(): ?string
    {
        $path = $this->get('og_image_path');

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Social profile URLs that are filled in, keyed by network, in SOCIAL_NETWORKS order.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $links = (array) $this->get('social', []);

        return collect(self::SOCIAL_NETWORKS)
            ->map(fn (string $label, string $network): ?string => $links[$network] ?? null)
            ->filter()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
        $this->resolved = null;
    }
}
