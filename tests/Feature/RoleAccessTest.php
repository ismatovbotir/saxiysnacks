<?php

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\RolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_has_its_own_page(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->get(route('admin.roles.index'))->assertOk()->assertSee('Warehouse')->assertSee(route('admin.roles.show', 'accountant'));

        foreach (UserRole::cases() as $role) {
            $this->get(route('admin.roles.show', $role))->assertOk()->assertSee($role->label());
        }

        $this->get(route('admin.roles.show', 'owner'))->assertSee('cannot be changed');
    }

    public function test_the_owner_changes_what_a_role_can_open(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));
        $warehouse = User::factory()->role(UserRole::Warehouse)->create();

        $this->put(route('admin.roles.update', 'warehouse'), ['permissions' => ['stock', 'products']])
            ->assertRedirect(route('admin.roles.show', 'warehouse'));

        $this->assertSame([AdminPermission::Products, AdminPermission::Stock], app(RolePermissions::class)->for(UserRole::Warehouse));

        $this->actingAs($warehouse);
        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.stock.movements'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertSee(route('admin.products.index'))->assertDontSee(route('admin.orders.index'));
    }

    public function test_owner_and_client_roles_cannot_be_edited(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put(route('admin.roles.update', 'owner'), ['permissions' => []])->assertForbidden();
        $this->put(route('admin.roles.update', 'client'), ['permissions' => ['orders']])->assertForbidden();
        $this->put(route('admin.roles.update', 'accountant'), ['permissions' => ['nope']])->assertSessionHasErrors('permissions.0');
    }

    public function test_only_roles_with_the_roles_page_can_change_access(): void
    {
        $this->actingAs(User::factory()->role(UserRole::SalesManager)->create());

        $this->get(route('admin.roles.index'))->assertForbidden();
        $this->put(route('admin.roles.update', 'warehouse'), ['permissions' => ['orders']])->assertForbidden();
    }
}
