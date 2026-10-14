<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Models\User;
use App\Support\Maps;
use App\Support\SiteSettings;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
        $this->admin = User::factory()->create(['email' => 'admin@saxiy.uz', 'password' => 'secret-password', 'locale' => 'en']);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.clients.type', TelegramClient::factory()->create()), ['type' => 'reseller'])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseHas('telegram_clients', ['type' => 'consumer']);
    }

    public function test_admins_can_log_in_and_out(): void
    {
        $this->post(route('admin.login.store'), ['email' => 'admin@saxiy.uz', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => "Email yoki parol noto'g'ri."]);
        $this->assertGuest();

        $this->post(route('admin.login.store'), ['email' => 'admin@saxiy.uz', 'password' => 'secret-password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_the_default_admin_is_seeded_from_env_settings(): void
    {
        config(['auth.default_admin' => ['name' => 'Owner', 'email' => 'owner@saxiy.uz', 'password' => 'from-env-secret']]);

        $this->seed(AdminUserSeeder::class);

        $this->post(route('admin.login.store'), ['email' => 'owner@saxiy.uz', 'password' => 'from-env-secret'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_no_default_admin_is_seeded_without_a_password(): void
    {
        config(['auth.default_admin' => ['name' => 'Owner', 'email' => 'owner@saxiy.uz', 'password' => null]]);

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'owner@saxiy.uz']);
    }

    public function test_every_admin_page_renders(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create();
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Курт', 'weight_grams' => 30, 'quantity' => 2, 'unit_price' => 1000, 'line_total' => 2000]);

        $this->actingAs($this->admin);

        $this->get(route('admin.orders.show', $order))->assertSee(Maps::yandex($order->delivery_latitude, $order->delivery_longitude));

        foreach ([
            route('admin.dashboard'),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $product->category),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.products.edit', $product),
            route('admin.clients.index'),
            route('admin.clients.show', $order->client),
            route('admin.orders.index'),
            route('admin.orders.show', $order),
            route('admin.settings.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_menu_is_grouped_by_business_area_with_a_new_orders_badge(): void
    {
        Order::factory()->count(3)->create(['status' => OrderStatus::New]);
        Order::factory()->create(['status' => OrderStatus::Completed]);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSeeInOrder(['Sales', 'Dashboard', 'Orders', 'Customers', 'Catalog', 'Products', 'Categories', 'Settings', 'Contacts &amp; social', 'Telegram bot', 'Admins'], false)
            ->assertSee('<span class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-ink" title="Needs attention">3</span>', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_creating_a_category_generates_its_slug(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'slug' => 'ignored-input',
                'name' => ['uz' => 'Pishloq', 'ru' => 'Сыр', 'en' => 'Cheese snacks'],
                'color' => '#F2A900',
                'sort_order' => 5,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'cheese-snacks', 'color' => '#F2A900', 'sort_order' => 5, 'is_active' => true]);
    }

    public function test_category_names_are_required_in_uzbek_and_russian(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => ['en' => 'Cheese'], 'color' => '#F2A900', 'sort_order' => 0])
            ->assertSessionHasErrors(['name.uz', 'name.ru']);

        $this->assertDatabaseEmpty('categories');
    }

    public function test_a_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $product->category))
            ->assertSessionHas('error');

        $this->assertModelExists($product->category);
    }

    public function test_creating_a_product_stores_the_photo_and_parses_the_price(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => ['uz' => 'Keshyu', 'ru' => 'Кешью', 'en' => 'Cashew'],
                'weight_grams' => 60,
                'box_quantity' => 20,
                'consumer_price' => '185 000',
                'reseller_price' => '160 000',
                'sort_order' => 0,
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('cashew.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::sole();
        $this->assertSame('cashew-60g', $product->slug);
        $this->assertSame(185000, $product->consumer_price);
        $this->assertSame(160000, $product->reseller_price);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_photos_must_be_images(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'category_id' => Category::factory()->create()->id,
                'name' => ['uz' => 'Keshyu', 'ru' => 'Кешью'],
                'weight_grams' => 60,
                'sort_order' => 0,
                'image' => UploadedFile::fake()->create('cashew.php', 10, 'application/x-php'),
            ])
            ->assertSessionHasErrors(['image']);

        $this->assertDatabaseEmpty('products');
    }

    public function test_updating_a_product_keeps_its_slug_and_can_remove_the_photo(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['image_path' => UploadedFile::fake()->image('old.jpg')->store('products', 'public')]);
        $oldImage = $product->image_path;
        $slug = $product->slug;

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), [
                'category_id' => $product->category_id,
                'name' => ['uz' => 'Yangi', 'ru' => 'Новое', 'en' => 'Renamed'],
                'weight_grams' => $product->weight_grams,
                'sort_order' => 0,
                'remove_image' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame($slug, $product->slug);
        $this->assertNull($product->image_path);
        $this->assertNull($product->consumer_price);
        $this->assertNull($product->reseller_price);
        $this->assertFalse($product->is_active);
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_marking_a_customer_as_reseller_notifies_them_and_updates_the_admin_chat(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001, 'admin_message_id' => 777]);

        $this->actingAs($this->admin)
            ->from(route('admin.clients.show', $client))
            ->patch(route('admin.clients.type', $client), ['type' => 'reseller'])
            ->assertRedirect(route('admin.clients.show', $client))
            ->assertSessionHas('status', "{$client->fullName()} now gets Reseller prices.");

        $this->assertSame(ClientType::Reseller, $client->refresh()->type);
        $this->assertTelegramCalled('editMessageText', fn (array $data): bool => $data['message_id'] === 777);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], 'цены для реселлеров'));
    }

    public function test_client_type_must_be_a_known_value(): void
    {
        $client = TelegramClient::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.type', $client), ['type' => 'vip'])
            ->assertSessionHasErrors(['type']);

        $this->assertSame(ClientType::Consumer, $client->refresh()->type);
    }

    public function test_blocking_a_customer_notifies_them(): void
    {
        $client = TelegramClient::factory()->create(['telegram_id' => 1001]);

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.status', $client), ['status' => 'blocked'])
            ->assertSessionHas('status');

        $this->assertSame(ClientStatus::Blocked, $client->refresh()->status);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === 1001
            && str_contains($data['text'], 'заблокирован'));
    }

    public function test_changing_an_order_status_notifies_the_client(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertSessionHas('status', "Order {$order->number()} is now Shipped.");

        $this->assertSame(OrderStatus::Shipped, $order->refresh()->status);
        $this->assertTelegramCalled('sendMessage', fn (array $data): bool => $data['chat_id'] === $order->client->telegram_id
            && str_contains($data['text'], 'отправлен'));
    }

    public function test_contacts_and_social_links_can_be_updated(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'brand_name' => 'SAXIY',
                'legal_name' => ['uz' => 'QK', 'ru' => 'СП', 'en' => 'JV'],
                'tagline' => ['uz' => 'a', 'ru' => 'b', 'en' => 'c'],
                'address' => ['uz' => 'Toshkent', 'ru' => 'Ташкент', 'en' => 'Tashkent'],
                'working_hours' => ['uz' => '', 'ru' => '', 'en' => ''],
                'phones' => ['+998 71 200-00-00', '', ''],
                'email' => 'sales@saxiy.uz',
                'telegram_contact' => 'https://t.me/saxiy_sales',
                'social' => ['instagram' => 'https://instagram.com/saxiy.uz', 'linkedin' => 'https://linkedin.com/company/saxiy', 'telegram' => ''],
                'og_image' => UploadedFile::fake()->image('share.png', 1200, 630),
            ])
            ->assertSessionHas('status');

        $settings = app(SiteSettings::class);
        $this->assertSame(['+998 71 200-00-00'], $settings->get('phones'));
        $this->assertSame('saxiy_sales', $settings->get('telegram_contact'));
        $this->assertSame('https://t.me/saxiy_sales?text=Salom%21', $settings->telegramChatUrl('Salom!'));
        $this->assertSame(['instagram' => 'https://instagram.com/saxiy.uz', 'linkedin' => 'https://linkedin.com/company/saxiy'], $settings->socialLinks());
        Storage::disk('public')->assertExists($settings->get('og_image_path'));
    }

    public function test_the_telegram_contact_must_be_a_username(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'brand_name' => 'SAXIY',
                'legal_name' => [], 'tagline' => [], 'address' => [], 'working_hours' => [],
                'telegram_contact' => '+998 90 123 45 67',
            ])
            ->assertSessionHasErrors(['telegram_contact' => 'Enter a Telegram username like saxiy_sales (5–32 letters, digits or underscores).']);
    }

    public function test_social_links_must_be_https_urls(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'brand_name' => 'SAXIY',
                'legal_name' => [], 'tagline' => [], 'address' => [], 'working_hours' => [],
                'social' => ['instagram' => 'javascript:alert(1)'],
            ])
            ->assertSessionHasErrors(['social.instagram']);
    }
}
