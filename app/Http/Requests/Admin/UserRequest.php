<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * The password is required for new staff; when editing, an empty password keeps the current one.
     * Client accounts are not managed here.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults(), 'confirmed'],
            'phone' => ['nullable', 'string', 'regex:/^\d{10,15}$/', Rule::unique('users')->ignore($user)],
            'unlink_telegram' => ['boolean'],
            'role' => ['required', Rule::enum(UserRole::class)->only(UserRole::staff())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => Phone::normalize($this->input('phone')) ?? (filled($this->input('phone')) ? (string) $this->input('phone') : null),
            'unlink_telegram' => $this->boolean('unlink_telegram'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter the phone number with the country code, e.g. +998 90 123 45 67.'),
            'phone.unique' => __('Another person already uses this phone number.'),
        ];
    }
}
