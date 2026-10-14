<?php

namespace App\Http\Requests\WebApp;

use App\Models\TelegramClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->client()?->isActive();
    }

    /**
     * Consumers choose a delivery location on every order; resellers deliver to their saved location.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $asksForLocation = ! $this->usesSavedLocation();

        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'latitude' => [Rule::requiredIf($asksForLocation), 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($asksForLocation), 'nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Please choose a delivery location.',
            'longitude.required' => 'Please choose a delivery location.',
        ];
    }

    public function usesSavedLocation(): bool
    {
        $client = $this->client();

        return $client !== null && $client->isReseller() && $client->hasLocation();
    }

    private function client(): ?TelegramClient
    {
        return $this->attributes->get('telegram_client');
    }
}
