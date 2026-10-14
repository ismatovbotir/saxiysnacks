<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDevLoginTest extends TestCase
{
    use RefreshDatabase;

    private function runningIn(string $environment, bool $autoLogin): void
    {
        $this->app->detectEnvironment(fn (): string => $environment);
        config(['auth.dev_auto_login' => $autoLogin, 'auth.default_admin.email' => 'admin@saxiy.uz']);
    }

    public function test_local_development_opens_the_admin_panel_as_the_default_admin(): void
    {
        User::factory()->create(['email' => 'first@saxiy.uz']);
        $admin = User::factory()->create(['email' => 'admin@saxiy.uz']);
        $this->runningIn('local', true);

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('DEV');
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_the_first_admin_is_used_when_the_default_one_does_not_exist(): void
    {
        $first = User::factory()->create();
        $this->runningIn('local', true);

        $this->get(route('admin.orders.index'))->assertOk();
        $this->assertAuthenticatedAs($first);
    }

    public function test_the_login_is_required_without_the_flag(): void
    {
        User::factory()->create(['email' => 'admin@saxiy.uz']);
        $this->runningIn('local', false);

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_the_flag_is_ignored_outside_local_development(): void
    {
        User::factory()->create(['email' => 'admin@saxiy.uz']);

        foreach (['production', 'staging', 'testing'] as $environment) {
            $this->runningIn($environment, true);

            $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
            $this->assertGuest();
        }
    }
}
