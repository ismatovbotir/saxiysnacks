<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Support\Phone;
use App\Support\RolePermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'telegram_chat_id', 'role', 'telegram_client_id', 'locale', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'telegram_chat_id' => 'integer',
        ];
    }

    /**
     * Stored as digits with the country code, so typed and Telegram-shared numbers match.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => Phone::normalize($value));
    }

    public function hasTelegramLinked(): bool
    {
        return $this->telegram_chat_id !== null;
    }

    /**
     * The customer a client account belongs to (for the future customer cabinet).
     *
     * @return BelongsTo<TelegramClient, $this>
     */
    public function telegramClient(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class);
    }

    /**
     * Only staff accounts with an email address may use the back office (by password or Telegram code).
     */
    public function canUseBackOffice(): bool
    {
        return $this->role->isStaff() && filled($this->email);
    }

    public function canAccess(AdminPermission $permission): bool
    {
        return $this->canUseBackOffice() && app(RolePermissions::class)->allows($this->role, $permission);
    }
}
