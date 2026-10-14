<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class AdminLocaleTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    public function test_the_admin_panel_is_in_uzbek_by_default(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<html lang="uz"', false)
            ->assertSee('Bosh sahifa')
            ->assertSee('Savdo')
            ->assertSee('Buyurtmalar');
    }

    public function test_an_admin_can_switch_to_russian_and_it_is_remembered(): void
    {
        $admin = User::factory()->create();
        Order::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->put(route('admin.locale'), ['locale' => 'ru'])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('ru', $admin->fresh()->locale);

        $this->flushSession();

        $this->actingAs($admin->fresh())
            ->get(route('admin.orders.index'))
            ->assertSee('<html lang="ru"', false)
            ->assertSee('Заказы')
            ->assertSee('Продажи')
            ->assertSee('1 заказ')
            ->assertSee('Новый');
    }

    public function test_the_login_page_language_can_be_switched_before_signing_in(): void
    {
        $this->get(route('admin.login'))->assertSee('Kirish');

        $this->put(route('admin.locale'), ['locale' => 'ru'])->assertRedirect();

        $this->get(route('admin.login'))->assertSee('Войти')->assertSee('Запомнить меня');
        $this->post(route('admin.login.store'), ['email' => 'nobody@saxiy.uz', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'Неверный email или пароль.']);
    }

    public function test_unknown_languages_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.locale'), ['locale' => 'de'])
            ->assertSessionHasErrors('locale');
    }

    public function test_validation_and_status_messages_are_translated(): void
    {
        $this->configureTelegram();
        $this->actingAs(User::factory()->create(['locale' => 'ru']));

        $this->post(route('admin.products.store'), ['class_code' => '123'])
            ->assertSessionHasErrors([
                'category_id' => 'Заполните поле «Категория».',
                'class_code' => 'Код ИКПУ должен состоять ровно из 17 цифр.',
            ]);

        $order = Order::factory()->create();
        $this->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertSessionHas('status', "Заказ {$order->number()} теперь: Подтверждён.");
    }

    public function test_every_admin_page_renders_in_uzbek_and_russian(): void
    {
        $this->configureTelegram();
        $product = Product::factory()->create();
        $order = Order::factory()->create();
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Курт', 'weight_grams' => 30, 'box_quantity' => 40, 'quantity' => 2, 'unit_price' => 1000, 'line_total' => 2000]);

        foreach (['uz', 'ru'] as $locale) {
            $admin = User::factory()->create(['locale' => $locale]);
            $this->actingAs($admin);

            foreach ([
                route('admin.dashboard'),
                route('admin.orders.index'),
                route('admin.orders.show', $order),
                route('admin.clients.index'),
                route('admin.clients.show', $order->client),
                route('admin.products.index'),
                route('admin.products.create'),
                route('admin.products.edit', $product),
                route('admin.categories.index'),
                route('admin.categories.create'),
                route('admin.settings.edit'),
                route('admin.telegram.edit'),
                route('admin.users.index'),
                route('admin.users.edit', $admin),
            ] as $url) {
                $this->get($url)
                    ->assertOk()
                    ->assertSee('<html lang="'.$locale.'"', false)
                    ->assertDontSee(':count')
                    ->assertDontSee(['Log out', 'View site', 'Save', 'Delete']);
            }
        }
    }
}
