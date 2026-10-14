<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Services\Telegram\AdminChatMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class AdminChatOrderMessageTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    public function test_a_new_order_is_posted_to_the_admin_chat_with_full_details(): void
    {
        $this->configureTelegram();
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:42:00', 'UTC'));

        $client = TelegramClient::factory()->reseller()->create([
            'telegram_id' => 1001,
            'first_name' => 'Aziz',
            'last_name' => 'Karimov',
            'telegram_username' => 'aziz_shop',
            'phone' => '+998 90 123 45 67',
            'company_name' => 'Aziz Market',
            'vat_number' => '305123456',
            'latitude' => 41.35,
            'longitude' => 69.28,
            'address' => 'Warehouse 4',
        ]);
        Order::factory()->for($client, 'client')->create();
        $kurt = Product::factory()->create([
            'sku' => 'KRT-30',
            'name' => ['uz' => 'Qurt', 'ru' => 'Курт классический', 'en' => 'Kurt'],
            'weight_grams' => 30,
            'box_quantity' => 40,
            'reseller_price' => 100000,
        ]);

        $this->postJson(route('webapp.orders.store'), [
            'items' => [['product_id' => $kurt->id, 'quantity' => 3]],
            'comment' => 'Call before arriving',
        ], $this->webAppHeaders(1001))->assertCreated();

        $order = Order::latest('id')->first();

        $this->assertTelegramCalled('sendMessage', function (array $data) use ($order): bool {
            if ($data['chat_id'] !== self::ADMIN_CHAT_ID) {
                return false;
            }

            foreach ([
                "Order {$order->number()}",
                '14.10.2026 15:42 (Tashkent)',
                '<a href="tg://user?id=1001">Aziz Karimov</a> (@aziz_shop)',
                '+998 90 123 45 67',
                'Aziz Market · VAT 305123456',
                'Reseller prices · 🔁 1 earlier order',
                'Warehouse 4',
                '1. <b>Курт классический</b>, 30 g · <code>KRT-30</code>',
                '3 packages × 40 pcs = 120 pcs',
                "100 000 so'm / package → <b>300 000 so'm</b>",
                '<b>Packages:</b> 3 · <b>Pieces:</b> 120 · <b>Weight:</b> ≈3.6 kg',
                "Total: 300 000 so'm",
                'Call before arriving',
            ] as $expected) {
                $this->assertStringContainsString($expected, $data['text']);
            }

            return true;
        });
    }

    public function test_orders_with_many_lines_are_shortened_to_fit_one_telegram_message(): void
    {
        $order = Order::factory()->create();

        foreach (range(1, 80) as $index) {
            $order->items()->create([
                'product_id' => Product::factory()->create()->id,
                'product_name' => "Очень длинное название продукта номер {$index} для проверки лимита",
                'weight_grams' => 30,
                'box_quantity' => 40,
                'quantity' => 2,
                'unit_price' => 100000,
                'line_total' => 200000,
            ]);
        }

        $text = app(AdminChatMessages::class)->orderText($order->fresh());

        $this->assertLessThanOrEqual(4096, mb_strlen(strip_tags($text)));
        $this->assertMatchesRegularExpression('/… and \d+ more — see the admin panel/', $text);
        $this->assertStringContainsString('<b>Pieces:</b> 160', $text);
    }
}
