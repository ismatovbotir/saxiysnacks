<?php

namespace App\Http\Controllers\Telegram;

use App\Actions\ChangeClientStatus;
use App\Actions\ChangeClientType;
use App\Actions\ChangeOrderStatus;
use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SyncClientAdminMessage;
use App\Models\Order;
use App\Models\TelegramClient;
use App\Services\Telegram\AdminChatMessages;
use App\Services\Telegram\ClientMessages;
use App\Services\Telegram\TelegramBot;
use App\Support\BotLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        private TelegramBot $bot,
        private ClientMessages $clientMessages,
        private AdminChatMessages $adminMessages,
        private ChangeClientType $changeClientType,
        private ChangeClientStatus $changeClientStatus,
        private ChangeOrderStatus $changeOrderStatusAction,
    ) {}

    public function __invoke(Request $request): Response
    {
        $secret = (string) config('services.telegram.webhook_secret');

        abort_if($secret === '' || ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        if ($request->has('callback_query')) {
            $this->handleCallbackQuery($request->input('callback_query'));
        } elseif ($request->has('message')) {
            $this->handleMessage($request->input('message'));
        }

        return response()->noContent();
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function handleMessage(array $message): void
    {
        if (($message['chat']['type'] ?? null) !== 'private') {
            return;
        }

        $locale = BotLocale::fromTelegram($message['from']['language_code'] ?? null);
        $client = TelegramClient::firstWhere('telegram_id', $message['from']['id']);

        if (isset($message['location'])) {
            $this->saveSharedLocation($message, $client, $locale);

            return;
        }

        if (! str_starts_with((string) ($message['text'] ?? ''), '/start')) {
            return;
        }

        $this->bot->sendMessage(
            $message['chat']['id'],
            $this->clientMessages->welcome($client, $client?->language_code ?? $locale),
            $this->clientMessages->catalogKeyboard($client?->language_code ?? $locale),
        );
    }

    /**
     * A location shared in the chat (📎 → Location) becomes the customer's saved delivery location.
     *
     * @param  array<string, mixed>  $message
     */
    private function saveSharedLocation(array $message, ?TelegramClient $client, string $locale): void
    {
        if ($client === null || ! $client->isActive()) {
            $this->bot->sendMessage(
                $message['chat']['id'],
                $this->clientMessages->welcome($client, $client?->language_code ?? $locale),
                $this->clientMessages->catalogKeyboard($client?->language_code ?? $locale),
            );

            return;
        }

        $client->moveTo((float) $message['location']['latitude'], (float) $message['location']['longitude']);

        SyncClientAdminMessage::dispatch($client);

        $this->bot->sendMessage(
            $message['chat']['id'],
            $this->clientMessages->locationSaved($client),
            $this->clientMessages->catalogKeyboard($client->language_code),
        );
    }

    /**
     * Handles the customer (reseller / block) and order status buttons in the admin chat.
     *
     * @param  array<string, mixed>  $callback
     */
    private function handleCallbackQuery(array $callback): void
    {
        if ((string) ($callback['message']['chat']['id'] ?? '') !== (string) config('services.telegram.admin_chat_id')) {
            $this->bot->answerCallbackQuery($callback['id'], 'Not allowed.', true);

            return;
        }

        [$entity, $action, $uuid] = array_pad(explode(':', (string) ($callback['data'] ?? ''), 3), 3, null);

        $answer = match ($entity) {
            'client' => $this->updateClient((string) $action, (string) $uuid),
            'order' => $this->changeOrderStatus((string) $action, (string) $uuid),
            default => 'Unknown action.',
        };

        $this->bot->answerCallbackQuery($callback['id'], $answer);
    }

    private function updateClient(string $action, string $uuid): string
    {
        $client = TelegramClient::firstWhere('uuid', $uuid);

        if ($client === null) {
            return 'Customer not found.';
        }

        match ($action) {
            'reseller' => $this->changeClientType->handle($client, ClientType::Reseller),
            'consumer' => $this->changeClientType->handle($client, ClientType::Consumer),
            'block' => $this->changeClientStatus->handle($client, ClientStatus::Blocked),
            'unblock' => $this->changeClientStatus->handle($client, ClientStatus::Active),
            default => null,
        };

        return "{$client->fullName()}: {$client->type->label()}, {$client->status->label()}";
    }

    private function changeOrderStatus(string $action, string $uuid): string
    {
        $order = Order::with('client')->firstWhere('uuid', $uuid);
        $status = OrderStatus::tryFrom($action);

        if ($order === null || $status === null) {
            return 'Order not found.';
        }

        if (! in_array($status, $this->adminMessages->nextOrderStatuses($order->status), true)) {
            return "Order is already {$order->status->label()}.";
        }

        $this->changeOrderStatusAction->handle($order, $status);

        return "{$order->number()}: {$status->label()}";
    }
}
