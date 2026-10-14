<?php

namespace App\Services\Telegram;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Telegram Bot API.
 *
 * @see https://core.telegram.org/bots/api
 */
class TelegramBot
{
    public function __construct(private ?string $token) {}

    public function isConfigured(): bool
    {
        return filled($this->token);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>|bool
     */
    public function call(string $method, array $parameters = []): array|bool
    {
        return $this->client()
            ->post($method, array_filter($parameters, fn (mixed $value): bool => $value !== null))
            ->throw()
            ->json('result');
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     * @return array<string, mixed>
     */
    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): array
    {
        return $this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'link_preview_options' => ['is_disabled' => true],
            'reply_markup' => $replyMarkup,
        ]);
    }

    /**
     * Send a native map pin, optionally as a reply to another message.
     */
    public function sendLocation(int|string $chatId, float $latitude, float $longitude, ?int $replyToMessageId = null): void
    {
        $this->call('sendLocation', [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'reply_parameters' => $replyToMessageId ? ['message_id' => $replyToMessageId, 'allow_sending_without_reply' => true] : null,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     */
    public function editMessageText(int|string $chatId, int $messageId, string $text, ?array $replyMarkup = null): void
    {
        $this->call('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'link_preview_options' => ['is_disabled' => true],
            'reply_markup' => $replyMarkup ?? ['inline_keyboard' => []],
        ]);
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void
    {
        $this->call('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl("https://api.telegram.org/bot{$this->token}/")
            ->asJson()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(10);
    }
}
