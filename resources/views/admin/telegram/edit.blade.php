<x-admin.layout :title="__('Telegram bot')">
    <x-admin.page-header :title="__('Telegram bot')" :subtitle="__('Bot token, webhook, admin chat and the message queue.')" />

    @php
        $webhookConnected = ($webhook['url'] ?? null) === $webhookUrl;
        $code = fn (string $text): string => '<code class="rounded bg-black/5 px-1 font-mono">'.e($text).'</code>';
    @endphp

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="admin-card space-y-4 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">1. {{ __('Bot account') }}</h2>
                @if ($settings->botUsername())
                    <a href="https://t.me/{{ $settings->botUsername() }}" target="_blank" class="admin-badge ring-black/5 bg-leaf/10 text-sm font-medium text-leaf-dark">✓ {{ '@'.$settings->botUsername() }}</a>
                @else
                    <span class="admin-badge ring-black/5 bg-black/5 text-sm text-black/60">{{ __('Not connected') }}</span>
                @endif
            </div>
            <p class="text-sm text-black/60">{!! __('In Telegram open :botfather, send :newbot (or :token for an existing bot) and paste the token here. It is checked with Telegram and stored encrypted.', [
                'botfather' => '<a href="https://t.me/BotFather" target="_blank" class="underline">@BotFather</a>',
                'newbot' => $code('/newbot'),
                'token' => $code('/token'),
            ]) !!}</p>
            <form method="POST" action="{{ route('admin.telegram.bot') }}" class="space-y-3">
                @csrf @method('PUT')
                <x-admin.field :label="__('Bot token')" name="bot_token">
                    <input type="password" name="bot_token" required autocomplete="off" placeholder="{{ $settings->isConfigured() ? __('Saved — paste a new token to replace it') : '123456789:AAH…' }}" class="admin-input font-mono">
                </x-admin.field>
                <button class="admin-btn">{{ $settings->isConfigured() ? __('Replace token') : __('Connect bot') }}</button>
            </form>
        </section>

        <section class="admin-card space-y-4 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">2. {{ __('Webhook') }}</h2>
                @if ($webhookConnected)
                    <span class="admin-badge ring-black/5 bg-leaf/10 text-sm font-medium text-leaf-dark">✓ {{ __('Connected') }}</span>
                @else
                    <span class="admin-badge ring-black/5 bg-black/5 text-sm text-black/60">{{ __('Not connected') }}</span>
                @endif
            </div>
            <p class="text-sm text-black/60">{{ __('Telegram sends messages, button presses and shared locations to this address. Connecting also sets the bot\'s “Catalog” menu button and the /start command.') }}</p>
            <div class="rounded-lg bg-black/5 px-3 py-2 font-mono text-xs break-all">{{ $webhookUrl }}</div>

            @if (! $canConnect)
                <p class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900">{!! __('Telegram only delivers to HTTPS addresses. Set :app_url in .env to your public https:// domain to connect.', ['app_url' => $code('APP_URL')]) !!}</p>
            @endif
            @if ($webhookError)
                <p class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">{{ __('Could not read the webhook status: :error', ['error' => $webhookError]) }}</p>
            @endif
            @if ($webhook && filled($webhook['url'] ?? null) && ! $webhookConnected)
                <p class="text-sm text-amber-800">{{ __('The bot currently delivers to another address:') }} <span class="font-mono break-all">{{ $webhook['url'] }}</span></p>
            @endif
            @if ($webhook && ($webhook['pending_update_count'] ?? 0) > 0)
                <p class="text-sm text-black/60">{{ __('Waiting updates: :count', ['count' => $webhook['pending_update_count']]) }}</p>
            @endif
            @if ($webhook && filled($webhook['last_error_message'] ?? null))
                <p class="text-sm text-red-700">{{ __('Last delivery error') }}{{ isset($webhook['last_error_date']) ? ' ('.\Illuminate\Support\Carbon::createFromTimestamp($webhook['last_error_date'])->diffForHumans().')' : '' }}: {{ $webhook['last_error_message'] }}</p>
            @endif

            <form method="POST" action="{{ route('admin.telegram.webhook') }}">
                @csrf
                <button class="admin-btn disabled:cursor-not-allowed disabled:opacity-40" @disabled(! $canConnect || ! $settings->isConfigured())>
                    {{ $webhookConnected ? __('Reconnect webhook') : __('Connect webhook') }}
                </button>
            </form>
        </section>

        <section class="admin-card space-y-4 p-5 sm:p-6 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">3. {{ __('Admin chat') }}</h2>
                @if ($settings->adminChatId())
                    <span class="admin-badge ring-black/5 bg-leaf/10 font-mono text-sm font-medium text-leaf-dark">✓ {{ $settings->adminChatId() }}</span>
                @else
                    <span class="admin-badge ring-black/5 bg-black/5 text-sm text-black/60">{{ __('Not linked') }}</span>
                @endif
            </div>
            <p class="text-sm text-black/60">{{ __('New customers and orders are posted here with buttons to make a customer a reseller, block them, or move an order to the next status.') }}</p>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold">{{ __('Link with a code') }}</h3>
                    <ol class="list-decimal space-y-1 pl-5 text-sm text-black/70">
                        <li>{{ __('Create a Telegram group for your team (or use an existing one).') }}</li>
                        <li>{{ __('Add :bot to the group.', ['bot' => $settings->botUsername() ? '@'.$settings->botUsername() : __('the bot')]) }}</li>
                        <li>{{ __('Send this message in the group:') }}</li>
                    </ol>
                    <div class="flex items-center gap-2">
                        <code id="link-command" class="flex-1 rounded-lg bg-ink px-3 py-2 font-mono text-sm text-white">/link {{ $linkCode }}</code>
                        <button type="button" class="admin-btn-outline px-3" onclick="navigator.clipboard.writeText(document.getElementById('link-command').textContent); this.textContent = @js(__('Copied'))">{{ __('Copy') }}</button>
                    </div>
                    <p class="text-xs text-black/50">{{ __('The code works once and expires in 30 minutes. The webhook must be connected. Reload this page after the bot confirms.') }}</p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold">{{ __('Or enter the chat ID') }}</h3>
                    <form method="POST" action="{{ route('admin.telegram.admin-chat') }}" class="flex flex-wrap items-start gap-2">
                        @csrf @method('PUT')
                        <div class="min-w-0 flex-1">
                            <x-admin.field :label="__('Chat ID')" name="admin_chat_id">
                                <input type="text" name="admin_chat_id" inputmode="numeric" value="{{ old('admin_chat_id', $settings->adminChatId()) }}" placeholder="-1001234567890" class="admin-input font-mono">
                            </x-admin.field>
                        </div>
                        <button class="admin-btn mt-6">{{ __('Save') }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.telegram.test') }}">
                        @csrf
                        <button class="admin-btn-outline disabled:cursor-not-allowed disabled:opacity-40" @disabled(! $settings->isConfigured() || ! $settings->adminChatId())>{{ __('Send a test message') }}</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="admin-card space-y-3 p-5 sm:p-6 lg:col-span-2">
            @php
                $stuck = $queue['oldestPendingAt']?->lt(now()->subMinute());
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">4. {{ __('Message queue') }}</h2>
                @if ($queue['connection'] === 'sync')
                    <span class="admin-badge ring-black/5 bg-black/5 text-sm text-black/60">{{ __('Sent immediately (sync)') }}</span>
                @elseif ($stuck)
                    <span class="admin-badge ring-black/5 bg-red-50 text-sm font-medium text-red-800">⚠ {{ __('Worker not running') }}</span>
                @elseif ($queue['pending'] !== null)
                    <span class="admin-badge ring-black/5 bg-leaf/10 text-sm font-medium text-leaf-dark">✓ {{ __('No backlog') }}</span>
                @endif
            </div>
            <p class="text-sm text-black/60">{{ __('Order and customer posts to the admin chat, and messages to customers, are sent in the background by the queue worker. The test message above is sent directly, so it works even when the worker is stopped.') }}</p>
            @if ($queue['pending'] !== null)
                <p class="text-sm"><b>{{ __('Waiting to be sent:') }}</b> {{ $queue['pending'] }}@if ($queue['oldestPendingAt']) · {{ __('oldest :time', ['time' => $queue['oldestPendingAt']->diffForHumans()]) }}@endif</p>
            @endif
            @if ($stuck)
                <p class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">{!! __('Messages are waiting but nothing is sending them. On the server run :command and keep it running (Supervisor or systemd).', ['command' => $code('php artisan queue:work')]) !!}</p>
            @endif
            @if ($queue['failed'] > 0)
                <p class="text-sm text-red-700"><b>{{ __('Failed:') }}</b> {{ $queue['failed'] }} — {{ __('last error:') }} <span class="font-mono text-xs break-all">{{ $queue['lastFailure'] }}</span><br><span class="text-black/60">{!! __('Retry them with :command.', ['command' => $code('php artisan queue:retry all')]) !!}</span></p>
            @endif
        </section>
    </div>
</x-admin.layout>
