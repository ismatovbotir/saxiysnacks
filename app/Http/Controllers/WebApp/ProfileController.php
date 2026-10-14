<?php

namespace App\Http\Controllers\WebApp;

use App\Actions\RegisterTelegramClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebApp\RegisterClientRequest;
use App\Models\TelegramClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profile($request->attributes->get('telegram_client')));
    }

    public function store(RegisterClientRequest $request, RegisterTelegramClient $register): JsonResponse
    {
        $client = $register->handle($request->attributes->get('telegram_user'), $request->validated());

        return response()->json($this->profile($client), 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(?TelegramClient $client): array
    {
        return [
            'status' => $client?->status->value ?? 'guest',
            'type' => $client?->type->value,
            'client' => $client?->only(['first_name', 'last_name', 'phone', 'company_name', 'vat_number', 'latitude', 'longitude', 'address']),
        ];
    }
}
