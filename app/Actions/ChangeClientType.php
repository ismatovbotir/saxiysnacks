<?php

namespace App\Actions;

use App\Enums\ClientType;
use App\Enums\OrderUnit;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncClientAdminMessage;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;
use Illuminate\Support\Facades\DB;

class ChangeClientType
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Switch a client between consumer and reseller pricing, then update the admin chat post and tell the client.
     *
     * Consumers count in pieces and resellers in packages, so "My list" quantities are converted to the new unit.
     */
    public function handle(TelegramClient $client, ClientType $type): TelegramClient
    {
        if ($client->type === $type) {
            return $client;
        }

        DB::transaction(function () use ($client, $type): void {
            $client->update(['type' => $type]);
            $this->convertSavedQuantities($client, $type);
        });

        SyncClientAdminMessage::dispatch($client);
        SendTelegramMessage::dispatch(
            $client->telegram_id,
            $this->messages->typeChanged($client),
            $this->messages->catalogKeyboard($client->language_code),
        );

        return $client;
    }

    private function convertSavedQuantities(TelegramClient $client, ClientType $type): void
    {
        $client->savedProducts()->get()->each(function (Product $product) use ($client, $type): void {
            $quantity = (int) $product->pivot->quantity;
            $piecesPerPackage = max(1, (int) $product->box_quantity);

            $converted = $type->orderUnit() === OrderUnit::Package
                ? (int) ceil($quantity / $piecesPerPackage)
                : $quantity * $piecesPerPackage;

            $client->savedProducts()->updateExistingPivot($product->id, [
                'quantity' => min(10000, $product->allowedQuantity($type, $converted)),
            ]);
        });
    }
}
