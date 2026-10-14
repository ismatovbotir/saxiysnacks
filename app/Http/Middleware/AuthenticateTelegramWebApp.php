<?php

namespace App\Http\Middleware;

use App\Models\TelegramClient;
use App\Services\Telegram\InitDataValidator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates Mini App requests by the signed initData sent in the X-Telegram-Init-Data header.
 */
class AuthenticateTelegramWebApp
{
    public function __construct(private InitDataValidator $validator) {}

    public function handle(Request $request, Closure $next): Response
    {
        $telegramUser = $this->validator->validate((string) $request->header('X-Telegram-Init-Data'));

        if ($telegramUser === null) {
            abort(401, 'Open this page from the Telegram bot.');
        }

        $request->attributes->set('telegram_user', $telegramUser);
        $request->attributes->set(
            'telegram_client',
            TelegramClient::firstWhere('telegram_id', $telegramUser['id']),
        );

        return $next($request);
    }
}
