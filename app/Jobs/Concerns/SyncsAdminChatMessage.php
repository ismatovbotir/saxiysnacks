<?php

namespace App\Jobs\Concerns;

use App\Services\Telegram\TelegramBot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;

trait SyncsAdminChatMessage
{
    /**
     * Send the message once and store its id on the model; later calls edit that message in place.
     * Returns the id of a newly posted message, or null when an existing one was edited.
     *
     * @param  array<string, mixed>  $replyMarkup
     */
    protected function syncAdminMessage(TelegramBot $bot, Model $model, string $text, array $replyMarkup): ?int
    {
        $chatId = config('services.telegram.admin_chat_id');

        if (! $bot->isConfigured() || blank($chatId)) {
            return null;
        }

        if ($model->admin_message_id === null) {
            $message = $bot->sendMessage($chatId, $text, $replyMarkup);
            $model->forceFill(['admin_message_id' => $message['message_id']])->saveQuietly();

            return $message['message_id'];
        }

        try {
            $bot->editMessageText($chatId, $model->admin_message_id, $text, $replyMarkup);
        } catch (RequestException $exception) {
            if (! str_contains((string) $exception->response->json('description'), 'message is not modified')) {
                throw $exception;
            }
        }

        return null;
    }
}
