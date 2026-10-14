<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_only_opens_its_own_sections(): void
    {
        $expectations = [
            'sales_manager' => ['admin.orders.index' => 200, 'admin.clients.index' => 200, 'admin.products.index' => 200, 'admin.users.index' => 403, 'admin.settings.edit' => 403],
            'warehouse' => ['admin.orders.index' => 200, 'admin.clients.index' => 403, 'admin.products.index' => 403, 'admin.users.index' => 403],
            'accountant' => ['admin.orders.index' => 200, 'admin.clients.index' => 200, 'admin.products.index' => 403, 'admin.telegram.edit' => 403],
            'owner' => ['admin.orders.index' => 200, 'admin.products.index' => 200, 'admin.users.index' => 200, 'admin.telegram.edit' => 200],
        ];

        foreach ($expectations as $role => $routes) {
            $this->actingAs(User::factory()->role(UserRole::from($role))->create(['locale' => 'en']));

            $this->get(route('admin.dashboard'))->assertOk();

            foreach ($routes as $route => $status) {
                $this->get(route($route))->assertStatus($status);
            }
        }
    }

    public function test_the_menu_hides_sections_the_role_cannot_open(): void
    {
        $this->actingAs(User::factory()->role(UserRole::Warehouse)->create(['locale' => 'en']));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.orders.index'))
            ->assertDontSee(route('admin.users.index'))
            ->assertDontSee(route('admin.products.index'));
    }

    public function test_client_accounts_cannot_use_the_back_office(): void
    {
        $client = User::factory()->role(UserRole::Client)->create(['email' => 'client@example.com']);

        $this->post(route('admin.login.store'), ['email' => 'client@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($client)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_the_last_owner_cannot_be_demoted_or_deleted(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->role(UserRole::SalesManager)->create();
        $this->actingAs($owner);

        $this->put(route('admin.users.update', $owner), ['name' => $owner->name, 'email' => $owner->email, 'role' => 'sales_manager'])
            ->assertSessionHas('error');
        $this->assertSame(UserRole::Owner, $owner->fresh()->role);

        $this->put(route('admin.users.update', $manager), ['name' => $manager->name, 'email' => $manager->email, 'role' => 'accountant'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertSame(UserRole::Accountant, $manager->fresh()->role);

        $this->put(route('admin.users.update', $manager), ['name' => $manager->name, 'email' => $manager->email, 'role' => 'client'])
            ->assertSessionHasErrors('role');
    }
}
