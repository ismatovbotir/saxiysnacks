<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait InteractsWithTelegram
{
    protected const string BOT_TOKEN = '123456:TEST-TOKEN';

    protected const string ADMIN_CHAT_ID = '-100500';

    protected function configureTelegram(): void
    {
        config([
            'services.telegram.bot_token' => self::BOT_TOKEN,
            'services.telegram.webhook_secret' => 'webhook-secret',
            'services.telegram.admin_chat_id' => self::ADMIN_CHAT_ID,
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 777]]),
        ]);
    }

    /**
     * Build a Mini App initData string signed the way Telegram signs it.
     *
     * @param  array<string, mixed>  $user
     */
    protected function signedInitData(array $user, ?int $authDate = null, string $botToken = self::BOT_TOKEN): string
    {
        $fields = [
            'auth_date' => (string) ($authDate ?? now()->getTimestamp()),
            'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
            'user' => json_encode($user),
        ];

        ksort($fields);
        $dataCheckString = collect($fields)->map(fn (string $value, string $key): string => "{$key}={$value}")->implode("\n");
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $fields['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        return http_build_query($fields);
    }

    /**
     * @return array<string, string>
     */
    protected function webAppHeaders(int $telegramId, string $languageCode = 'ru'): array
    {
        return [
            'X-Telegram-Init-Data' => $this->signedInitData([
                'id' => $telegramId,
                'first_name' => 'Aziz',
                'username' => 'aziz_shop',
                'language_code' => $languageCode,
            ]),
        ];
    }

    /**
     * @param  callable(array<string, mixed>): bool  $matches
     */
    protected function assertTelegramCalled(string $method, callable $matches): void
    {
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), "/{$method}") && $matches($request->data()));
    }
}
