<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
    }

    /**
     * The code the bot sent to a chat, read from the faked Telegram request.
     */
    private function sentCode(int $chatId): ?string
    {
        $request = collect(Http::recorded())->map(fn (array $pair): Request => $pair[0])
            ->last(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage') && (int) $request['chat_id'] === $chatId);

        return $request && preg_match('/<b>(\d{6})<\/b>/', $request['text'], $matches) ? $matches[1] : null;
    }

    private function shareContact(int $telegramId, string $phone, ?int $contactUserId = null): TestResponse
    {
        return $this->postJson(route('telegram.webhook'), ['message' => [
            'message_id' => 1,
            'from' => ['id' => $telegramId, 'language_code' => 'en'],
            'chat' => ['id' => $telegramId, 'type' => 'private'],
            'contact' => ['phone_number' => $phone, 'user_id' => $contactUserId ?? $telegramId],
        ]], ['X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret']);
    }

    public function test_staff_link_telegram_by_sharing_their_own_number(): void
    {
        $user = User::factory()->create(['phone' => '+998 90 123-45-67']);
        $this->assertSame('998901234567', $user->phone);

        $this->shareContact(555, '+998901234567', contactUserId: 777)->assertNoContent();
        $this->assertNull($user->fresh()->telegram_chat_id);

        $this->shareContact(555, '998901234567')->assertNoContent();
        $this->assertSame(555, $user->fresh()->telegram_chat_id);

        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 555 && str_contains($data['text'], 'Linked'));
    }

    public function test_staff_sign_in_with_a_code_sent_by_the_bot(): void
    {
        $user = User::factory()->create(['phone' => '998901234567', 'telegram_chat_id' => 555, 'locale' => 'en']);

        $this->post(route('admin.login.code'), ['phone' => '+998 90 123 45 67'])->assertRedirect(route('admin.login', ['via' => 'phone']));
        $code = $this->sentCode(555);
        $this->assertNotNull($code);

        $this->post(route('admin.login.verify'), ['code' => $code === '000000' ? '111111' : '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post(route('admin.login.verify'), ['code' => $code])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_code_works_once_and_dies_after_too_many_wrong_guesses(): void
    {
        User::factory()->create(['phone' => '998901234567', 'telegram_chat_id' => 555]);
        $this->post(route('admin.login.code'), ['phone' => '998901234567']);
        $code = $this->sentCode(555);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.verify'), ['code' => $code === '999999' ? '999998' : '999999']);
        }

        $this->travel(61)->seconds();

        $this->post(route('admin.login.verify'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_unknown_numbers_get_the_same_answer_and_no_code(): void
    {
        $this->post(route('admin.login.code'), ['phone' => '+998 99 000 00 00'])
            ->assertRedirect(route('admin.login', ['via' => 'phone']))
            ->assertSessionHas('status');

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage'));
    }

    public function test_only_users_with_an_email_can_open_the_back_office(): void
    {
        $withoutEmail = User::factory()->create(['email' => null, 'phone' => '998901234567', 'telegram_chat_id' => 555]);

        $this->post(route('admin.login.code'), ['phone' => '998901234567']);
        $this->assertNull($this->sentCode(555));

        $this->actingAs($withoutEmail)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();

        $client = User::factory()->role(UserRole::Client)->create(['phone' => '998907777777', 'telegram_chat_id' => 777]);
        $this->post(route('admin.login.code'), ['phone' => '998907777777']);
        $this->assertNull($this->sentCode(777));
        $this->assertFalse($client->canUseBackOffice());
    }

    public function test_the_login_page_offers_both_methods(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('name="password"', false)->assertSee(route('admin.login', ['via' => 'phone']));
        $this->get(route('admin.login', ['via' => 'phone']))->assertOk()->assertSee('name="phone"', false);
    }
}
