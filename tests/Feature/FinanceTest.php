<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\TelegramClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->role(UserRole::Accountant)->create(['locale' => 'en']));
    }

    public function test_payments_are_recorded_against_an_order_up_to_the_amount_due(): void
    {
        $order = Order::factory()->create(['total' => 300000, 'status' => OrderStatus::Shipped]);

        $this->post(route('admin.finance.payments.store', $order), ['amount' => '200 000', 'method' => 'cash'])->assertSessionHas('status');
        $this->post(route('admin.finance.payments.store', $order), ['amount' => '150 000', 'method' => 'card'])->assertSessionHasErrors('amount');

        $this->assertSame(100000, $order->fresh()->load('payments')->amountDue());
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'telegram_client_id' => $order->telegram_client_id, 'amount' => 200000]);

        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Record payment');
        $this->get(route('admin.finance.payments.index'))->assertOk()->assertSee($order->number());
    }

    public function test_debts_list_customers_and_orders_with_money_owed(): void
    {
        $client = TelegramClient::factory()->reseller()->create(['company_name' => 'Aziz Market']);
        $shipped = Order::factory()->for($client, 'client')->create(['total' => 500000, 'status' => OrderStatus::Completed]);
        Order::factory()->for($client, 'client')->create(['total' => 900000, 'status' => OrderStatus::New]);
        Payment::factory()->for($shipped)->create(['telegram_client_id' => $client->id, 'amount' => 200000]);

        $this->get(route('admin.finance.debts'))
            ->assertOk()
            ->assertSee('Aziz Market')
            ->assertSee($shipped->number())
            ->assertSee("300 000 so'm")
            ->assertDontSee("900 000 so'm");
    }

    public function test_expenses_can_be_added_and_the_report_sums_the_period(): void
    {
        $this->post(route('admin.finance.expenses.store'), ['category' => 'packaging', 'amount' => '1 500 000', 'note' => 'Film rolls'])->assertSessionHas('status');
        Expense::factory()->create(['amount' => 999000, 'spent_on' => today()->subYear()]);
        Payment::factory()->create(['amount' => 4000000]);
        Order::factory()->create(['total' => 5000000]);
        Order::factory()->create(['total' => 7000000, 'status' => OrderStatus::Cancelled]);

        $this->get(route('admin.finance.expenses.index'))->assertOk()->assertSee('Film rolls')->assertDontSee("999 000 so'm");

        $this->get(route('admin.finance.report'))
            ->assertOk()
            ->assertSee("1 500 000 so'm")
            ->assertSee("4 000 000 so'm")
            ->assertSee("2 500 000 so'm")
            ->assertDontSee("7 000 000 so'm");
    }

    public function test_the_accountant_cannot_open_stock_or_settings(): void
    {
        $this->get(route('admin.stock.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }
}
