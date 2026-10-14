<?php

namespace App\Jobs;

use App\Services\Telegram\TelegramBot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [5, 30];

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     */
    public function __construct(
        public int $chatId,
        public string $text,
        public ?array $replyMarkup = null,
    ) {}

    public function handle(TelegramBot $bot): void
    {
        if (! $bot->isConfigured()) {
            return;
        }

        $bot->sendMessage($this->chatId, $this->text, $this->replyMarkup);
    }
}
