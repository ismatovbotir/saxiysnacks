<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use App\Models\TelegramClient;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Bot connection settings edited in the admin panel ("Telegram").
 * Values saved there win over the TELEGRAM_* values in .env; secrets are stored encrypted.
 */
class TelegramSettings
{
    private const string SETTING_KEY = 'telegram';

    private const string CACHE_KEY = 'telegram-settings';

    private const string LINK_CODE_CACHE_KEY = 'telegram-admin-chat-link-code';

    /**
     * @var array{bot_token?: string|null, bot_username?: string|null, webhook_secret?: string|null, admin_chat_id?: string|null}|null
     */
    private ?array $stored = null;

    public function botToken(): ?string
    {
        return $this->secret('bot_token') ?? config('services.telegram.bot_token');
    }

    public function botUsername(): ?string
    {
        $username = $this->stored()['bot_username'] ?? config('services.telegram.bot_username');

        return filled($username) ? ltrim($username, '@') : null;
    }

    public function webhookSecret(): ?string
    {
        return $this->secret('webhook_secret') ?? config('services.telegram.webhook_secret');
    }

    public function adminChatId(): ?string
    {
        $chatId = $this->stored()['admin_chat_id'] ?? config('services.telegram.admin_chat_id');

        return filled($chatId) ? (string) $chatId : null;
    }

    public function initDataTtl(): int
    {
        return (int) config('services.telegram.init_data_ttl', 86400);
    }

    public function isConfigured(): bool
    {
        return filled($this->botToken());
    }

    /**
     * Store a verified bot token with its username and a fresh webhook secret.
     */
    public function connectBot(string $token, string $username): void
    {
        $this->save([
            'bot_token' => Crypt::encryptString($token),
            'bot_username' => $username,
            'webhook_secret' => Crypt::encryptString(Str::random(48)),
        ]);
    }

    /**
     * Point admin notifications at another chat. Posts in the previous chat can no longer be edited,
     * so customers and orders get a fresh post in the new chat on their next update.
     */
    public function linkAdminChat(string $chatId): void
    {
        if ($chatId === $this->adminChatId()) {
            return;
        }

        $this->save(['admin_chat_id' => $chatId]);

        TelegramClient::query()->whereNotNull('admin_message_id')->update(['admin_message_id' => null]);
        Order::query()->whereNotNull('admin_message_id')->update(['admin_message_id' => null]);
    }

    /**
     * One-time code an admin sends as "/link CODE" in a group to make it the admin chat.
     */
    public function adminChatLinkCode(): string
    {
        return Cache::remember(self::LINK_CODE_CACHE_KEY, now()->addMinutes(30), fn (): string => Str::upper(Str::random(8)));
    }

    public function consumeAdminChatLinkCode(string $code): bool
    {
        $expected = Cache::get(self::LINK_CODE_CACHE_KEY);

        if (! is_string($expected) || ! hash_equals($expected, Str::upper($code))) {
            return false;
        }

        Cache::forget(self::LINK_CODE_CACHE_KEY);

        return true;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    private function save(array $values): void
    {
        Setting::updateOrCreate(['key' => self::SETTING_KEY], ['value' => array_replace($this->stored(), $values)]);

        Cache::forget(self::CACHE_KEY);
        $this->stored = null;
    }

    private function secret(string $key): ?string
    {
        $encrypted = $this->stored()[$key] ?? null;

        if (blank($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function stored(): array
    {
        return $this->stored ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => (array) Setting::query()->where('key', self::SETTING_KEY)->value('value'),
        );
    }
}
