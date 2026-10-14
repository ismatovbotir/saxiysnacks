<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ChangeClientStatus;
use App\Actions\ChangeClientType;
use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Models\TelegramClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $type = ClientType::tryFrom($request->string('type')->value());
        $status = ClientStatus::tryFrom($request->string('status')->value());

        $clients = TelegramClient::query()
            ->withCount('orders')
            ->when($type, fn ($query, ClientType $type) => $query->where('type', $type))
            ->when($status, fn ($query, ClientStatus $status) => $query->where('status', $status))
            ->when($request->string('search')->trim()->value(), fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"),
            ))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clients.index', ['clients' => $clients, 'type' => $type, 'status' => $status]);
    }

    public function show(TelegramClient $client): View
    {
        return view('admin.clients.show', [
            'client' => $client,
            'orders' => $client->orders()->latest('id')->limit(20)->get(),
        ]);
    }

    public function updateType(Request $request, TelegramClient $client, ChangeClientType $changeType): RedirectResponse
    {
        $validated = $request->validate(['type' => ['required', Rule::enum(ClientType::class)]]);

        $changeType->handle($client, ClientType::from($validated['type']));

        return back()->with('status', __(':name now gets :type prices.', ['name' => $client->fullName(), 'type' => $client->type->label()]));
    }

    public function updateStatus(Request $request, TelegramClient $client, ChangeClientStatus $changeStatus): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(ClientStatus::class)]]);

        $changeStatus->handle($client, ClientStatus::from($validated['status']));

        return back()->with('status', __(':name is now :status.', ['name' => $client->fullName(), 'status' => $client->status->label()]));
    }
}
