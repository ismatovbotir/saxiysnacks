<?php

namespace Tests\Unit;

use App\Services\Telegram\InitDataValidator;
use PHPUnit\Framework\TestCase;
use Tests\Concerns\InteractsWithTelegram;

class InitDataValidatorTest extends TestCase
{
    use InteractsWithTelegram;

    public function test_it_returns_the_user_for_correctly_signed_fresh_data(): void
    {
        $validator = new InitDataValidator(self::BOT_TOKEN, 3600);

        $user = $validator->validate($this->signedInitData(['id' => 42, 'first_name' => 'Aziz'], time()));

        $this->assertSame(['id' => 42, 'first_name' => 'Aziz'], $user);
    }

    public function test_it_rejects_data_signed_with_another_bot_token(): void
    {
        $validator = new InitDataValidator(self::BOT_TOKEN, 3600);

        $this->assertNull($validator->validate($this->signedInitData(['id' => 42], time(), '999:OTHER')));
    }

    public function test_it_rejects_tampered_data(): void
    {
        $validator = new InitDataValidator(self::BOT_TOKEN, 3600);
        $initData = str_replace('%22id%22%3A42', '%22id%22%3A43', $this->signedInitData(['id' => 42], time()));

        $this->assertNull($validator->validate($initData));
    }

    public function test_it_rejects_expired_data(): void
    {
        $validator = new InitDataValidator(self::BOT_TOKEN, 3600);

        $this->assertNull($validator->validate($this->signedInitData(['id' => 42], time() - 3601)));
    }

    public function test_it_rejects_everything_when_no_bot_token_is_configured(): void
    {
        $validator = new InitDataValidator(null, 3600);

        $this->assertNull($validator->validate($this->signedInitData(['id' => 42], time())));
    }
}
