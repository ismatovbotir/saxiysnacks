<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ConnectTelegramWebhook;
use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramBot;
use App\Support\TelegramSettings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class TelegramController extends Controller
{
    public function edit(TelegramSettings $settings, TelegramBot $bot): View
    {
        $webhook = null;
        $webhookError = null;

        if ($settings->isConfigured()) {
            try {
                $webhook = $bot->call('getWebhookInfo');
            } catch (ConnectionException|RequestException $exception) {
                $webhookError = $this->describe($exception);
            }
        }

        return view('admin.telegram.edit', [
            'settings' => $settings,
            'webhook' => is_array($webhook) ? $webhook : null,
            'webhookError' => $webhookError,
            'webhookUrl' => ConnectTelegramWebhook::webhookUrl(),
            'canConnect' => ConnectTelegramWebhook::canConnect(),
            'linkCode' => $settings->adminChatLinkCode(),
        ]);
    }

    /**
     * Verify a token with getMe, store it, and register the webhook when the site runs on HTTPS.
     */
    public function updateBot(Request $request, TelegramSettings $settings, ConnectTelegramWebhook $connect): RedirectResponse
    {
        $validated = $request->validate(
            ['bot_token' => ['required', 'string', 'regex:/^\d{5,16}:[A-Za-z0-9_-]{30,64}$/']],
            ['bot_token.regex' => 'This does not look like a bot token. Copy it from @BotFather, e.g. 123456789:AAH…'],
        );

        try {
            $bot = (new TelegramBot($validated['bot_token']))->call('getMe');
        } catch (ConnectionException|RequestException $exception) {
            throw ValidationException::withMessages(['bot_token' => 'Telegram did not accept this token: '.$this->describe($exception)]);
        }

        $settings->connectBot($validated['bot_token'], $bot['username']);

        if (! ConnectTelegramWebhook::canConnect()) {
            return back()->with('status', "Bot @{$bot['username']} saved. Connect the webhook once the site runs on HTTPS.");
        }

        return $this->connect($connect, "Bot @{$bot['username']} connected.");
    }

    public function connectWebhook(ConnectTelegramWebhook $connect): RedirectResponse
    {
        return $this->connect($connect, 'Webhook, menu button and /start command registered.');
    }

    public function updateAdminChat(Request $request, TelegramSettings $settings): RedirectResponse
    {
        $validated = $request->validate(
            ['admin_chat_id' => ['required', 'string', 'regex:/^-?\d{5,20}$/']],
            ['admin_chat_id.regex' => 'A chat ID is a number, e.g. -1001234567890 for a group.'],
        );

        $settings->linkAdminChat($validated['admin_chat_id']);

        return back()->with('status', 'Admin chat saved.');
    }

    public function testAdminChat(TelegramSettings $settings, TelegramBot $bot): RedirectResponse
    {
        if (! $settings->isConfigured() || $settings->adminChatId() === null) {
            return back()->with('error', 'Connect the bot and link an admin chat first.');
        }

        try {
            $bot->sendMessage($settings->adminChatId(), '👋 Test message from the SAXIY admin panel.');
        } catch (ConnectionException|RequestException $exception) {
            return back()->with('error', 'Could not post to the admin chat: '.$this->describe($exception));
        }

        return back()->with('status', 'Test message sent to the admin chat.');
    }

    private function connect(ConnectTelegramWebhook $connect, string $success): RedirectResponse
    {
        try {
            $connect->handle();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (ConnectionException|RequestException $exception) {
            return back()->with('error', 'Telegram rejected the webhook: '.$this->describe($exception));
        }

        return back()->with('status', $success);
    }

    private function describe(ConnectionException|RequestException $exception): string
    {
        return $exception instanceof RequestException
            ? (string) ($exception->response->json('description') ?? "HTTP {$exception->response->status()}")
            : 'Telegram could not be reached.';
    }
}
