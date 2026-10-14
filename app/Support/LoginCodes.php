<?php

namespace App\Support;

use App\Models\User;
use App\Services\Telegram\TelegramBot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * One-time 6-digit sign-in codes sent to a user's linked Telegram chat.
 * A code lives 5 minutes, allows 5 wrong guesses, and a new one can be requested once a minute.
 */
class LoginCodes
{
    public const int TTL_MINUTES = 5;

    public const int MAX_ATTEMPTS = 5;

    private const int RESEND_SECONDS = 60;

    public function __construct(private TelegramBot $bot) {}

    /**
     * Send a fresh code; false when the user has no linked chat, asked too recently, or Telegram failed.
     */
    public function send(User $user): bool
    {
        if ($user->telegram_chat_id === null || ! $this->bot->isConfigured() || ! Cache::add($this->cooldownKey($user), true, self::RESEND_SECONDS)) {
            return false;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->key($user), ['hash' => Hash::make($code), 'attempts' => 0], now()->addMinutes(self::TTL_MINUTES));

        try {
            $this->bot->sendMessage($user->telegram_chat_id, __('bot.login_code', ['code' => $code, 'minutes' => self::TTL_MINUTES], $user->locale ?? 'uz'));
        } catch (Throwable $exception) {
            report($exception);
            Cache::forget($this->key($user));

            return false;
        }

        return true;
    }

    /**
     * Check a code; a correct code is used up, and too many wrong ones burn it.
     */
    public function verify(User $user, string $code): bool
    {
        $entry = Cache::get($this->key($user));

        if ($entry === null || $entry['attempts'] >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $entry['hash'])) {
            Cache::put($this->key($user), [...$entry, 'attempts' => $entry['attempts'] + 1], now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        Cache::forget($this->key($user));

        return true;
    }

    private function key(User $user): string
    {
        return "admin-login-code:{$user->id}";
    }

    private function cooldownKey(User $user): string
    {
        return "admin-login-code-sent:{$user->id}";
    }
}
