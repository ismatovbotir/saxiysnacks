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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'queue' => $this->queueHealth(),
        ]);
    }

    /**
     * Verify a token with getMe, store it, and register the webhook when the site runs on HTTPS.
     */
    public function updateBot(Request $request, TelegramSettings $settings, ConnectTelegramWebhook $connect): RedirectResponse
    {
        $validated = $request->validate(
            ['bot_token' => ['required', 'string', 'regex:/^\d{5,16}:[A-Za-z0-9_-]{30,64}$/']],
            ['bot_token.regex' => __('This does not look like a bot token. Copy it from @BotFather, e.g. 123456789:AAH…')],
        );

        try {
            $bot = (new TelegramBot($validated['bot_token']))->call('getMe');
        } catch (ConnectionException|RequestException $exception) {
            throw ValidationException::withMessages(['bot_token' => __('Telegram did not accept this token: :reason', ['reason' => $this->describe($exception)])]);
        }

        $settings->connectBot($validated['bot_token'], $bot['username']);

        if (! ConnectTelegramWebhook::canConnect()) {
            return back()->with('status', __('Bot @:username saved. Connect the webhook once the site runs on HTTPS.', ['username' => $bot['username']]));
        }

        return $this->connect($connect, __('Bot @:username connected.', ['username' => $bot['username']]));
    }

    public function connectWebhook(ConnectTelegramWebhook $connect): RedirectResponse
    {
        return $this->connect($connect, __('Webhook, menu button and /start command registered.'));
    }

    public function updateAdminChat(Request $request, TelegramSettings $settings): RedirectResponse
    {
        $validated = $request->validate(
            ['admin_chat_id' => ['required', 'string', 'regex:/^-?\d{5,20}$/']],
            ['admin_chat_id.regex' => __('A chat ID is a number, e.g. -1001234567890 for a group.')],
        );

        $settings->linkAdminChat($validated['admin_chat_id']);

        return back()->with('status', __('Admin chat saved.'));
    }

    public function testAdminChat(TelegramSettings $settings, TelegramBot $bot): RedirectResponse
    {
        if (! $settings->isConfigured() || $settings->adminChatId() === null) {
            return back()->with('error', __('Connect the bot and link an admin chat first.'));
        }

        try {
            $bot->sendMessage($settings->adminChatId(), '👋 Test message from the SAXIY admin panel.');
        } catch (ConnectionException|RequestException $exception) {
            return back()->with('error', __('Could not post to the admin chat: :reason', ['reason' => $this->describe($exception)]));
        }

        return back()->with('status', __('Test message sent to the admin chat.'));
    }

    private function connect(ConnectTelegramWebhook $connect, string $success): RedirectResponse
    {
        try {
            $connect->handle();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (ConnectionException|RequestException $exception) {
            return back()->with('error', __('Telegram rejected the webhook: :reason', ['reason' => $this->describe($exception)]));
        }

        return back()->with('status', $success);
    }

    /**
     * Admin-chat posts and customer messages are queued; without a running worker they never leave.
     *
     * @return array{connection: string, pending: int|null, oldestPendingAt: Carbon|null, failed: int, lastFailure: string|null}
     */
    private function queueHealth(): array
    {
        $connection = (string) config('queue.default');
        $pending = null;
        $oldestPendingAt = null;

        if (config("queue.connections.{$connection}.driver") === 'database') {
            $jobs = DB::table(config("queue.connections.{$connection}.table", 'jobs'));
            $pending = $jobs->count();
            $oldest = $jobs->min('available_at');
            $oldestPendingAt = $oldest ? Carbon::createFromTimestamp($oldest) : null;
        }

        $lastFailure = DB::table(config('queue.failed.table', 'failed_jobs'))->latest('failed_at')->value('exception');

        return [
            'connection' => $connection,
            'pending' => $pending,
            'oldestPendingAt' => $oldestPendingAt,
            'failed' => DB::table(config('queue.failed.table', 'failed_jobs'))->count(),
            'lastFailure' => $lastFailure ? Str::limit(strtok($lastFailure, "\n"), 300) : null,
        ];
    }

    private function describe(ConnectionException|RequestException $exception): string
    {
        return $exception instanceof RequestException
            ? (string) ($exception->response->json('description') ?? "HTTP {$exception->response->status()}")
            : __('Telegram could not be reached.');
    }
}
