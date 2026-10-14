<?php

namespace App\Http\Requests\Admin;

use App\Support\SiteSettings;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'max:100'],
            'legal_name' => ['required', 'array:uz,ru,en'],
            'legal_name.*' => ['nullable', 'string', 'max:255'],
            'tagline' => ['required', 'array:uz,ru,en'],
            'tagline.*' => ['nullable', 'string', 'max:300'],
            'address' => ['required', 'array:uz,ru,en'],
            'address.*' => ['nullable', 'string', 'max:300'],
            'working_hours' => ['required', 'array:uz,ru,en'],
            'working_hours.*' => ['nullable', 'string', 'max:100'],
            'phones' => ['array', 'max:5'],
            'phones.*' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'telegram_contact' => ['nullable', 'string', 'regex:/^[A-Za-z][A-Za-z0-9_]{4,31}$/'],
            'map_url' => ['nullable', 'url:https', 'max:500'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'social' => ['array:'.implode(',', array_keys(SiteSettings::SOCIAL_NETWORKS))],
            'social.*' => ['nullable', 'url:https', 'max:255'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'telegram_contact.regex' => 'Enter a Telegram username like saxiy_sales (5–32 letters, digits or underscores).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phones' => array_values(array_filter((array) $this->input('phones', []), 'filled')),
            'telegram_contact' => preg_replace('#^(?:https?://)?(?:t\.me/|@)#i', '', trim((string) $this->input('telegram_contact'))) ?: null,
        ]);
    }
}
