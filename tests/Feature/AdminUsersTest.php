<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email' => 'admin@saxiy.uz', 'locale' => 'en']);
        $this->actingAs($this->admin);
    }

    public function test_admin_pages_render(): void
    {
        $other = User::factory()->create(['name' => 'Dilnoza']);

        $this->get(route('admin.users.index'))->assertOk()->assertSee('Dilnoza')->assertSee('admin@saxiy.uz');
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.edit', $other))->assertOk();
    }

    public function test_guests_cannot_manage_admins(): void
    {
        auth()->logout();

        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.users.store'), [])->assertRedirect(route('admin.login'));
    }

    public function test_a_new_admin_can_log_in(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'Dilnoza',
            'email' => ' Sales@Saxiy.uz ',
            'password' => 'new-admin-secret',
            'password_confirmation' => 'new-admin-secret',
        ])->assertRedirect(route('admin.users.index'));

        $created = User::firstWhere('email', 'sales@saxiy.uz');
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('new-admin-secret', $created->password));
    }

    public function test_new_admins_need_a_unique_email_and_a_confirmed_password(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'Copy',
            'email' => 'ADMIN@saxiy.uz',
            'password' => 'short',
            'password_confirmation' => 'other',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertSame(1, User::count());
    }

    public function test_an_empty_password_keeps_the_current_one(): void
    {
        $other = User::factory()->create(['password' => 'old-password-1']);

        $this->put(route('admin.users.update', $other), ['name' => 'Renamed', 'email' => $other->email, 'password' => ''])
            ->assertRedirect(route('admin.users.index'));

        $other->refresh();
        $this->assertSame('Renamed', $other->name);
        $this->assertTrue(Hash::check('old-password-1', $other->password));

        $this->put(route('admin.users.update', $other), [
            'name' => 'Renamed',
            'email' => $other->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $this->assertTrue(Hash::check('brand-new-password', $other->fresh()->password));
    }

    public function test_admins_can_delete_others_but_not_themselves(): void
    {
        $other = User::factory()->create();

        $this->delete(route('admin.users.destroy', $this->admin))->assertSessionHas('error');
        $this->assertModelExists($this->admin);

        $this->delete(route('admin.users.destroy', $other))->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($other);
    }
}
