<?php

namespace App\Services\Telegram;

/**
 * Verifies the signed initData string a Telegram Mini App receives on launch.
 *
 * @see https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 */
class InitDataValidator
{
    public function __construct(private ?string $botToken, private int $ttlSeconds) {}

    /**
     * Return the Telegram user when the payload is authentic and fresh, otherwise null.
     *
     * @return array{id: int, first_name?: string, last_name?: string, username?: string, language_code?: string}|null
     */
    public function validate(string $initData): ?array
    {
        if (blank($this->botToken) || $initData === '') {
            return null;
        }

        parse_str($initData, $fields);
        $hash = $fields['hash'] ?? null;

        if (! is_string($hash)) {
            return null;
        }

        unset($fields['hash']);
        ksort($fields);

        $dataCheckString = collect($fields)
            ->map(fn (mixed $value, string $key): string => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);

        if (! hash_equals(hash_hmac('sha256', $dataCheckString, $secretKey), $hash)) {
            return null;
        }

        if ((int) ($fields['auth_date'] ?? 0) < now()->getTimestamp() - $this->ttlSeconds) {
            return null;
        }

        $user = json_decode((string) ($fields['user'] ?? ''), true);

        return is_array($user) && isset($user['id']) ? $user : null;
    }
}
