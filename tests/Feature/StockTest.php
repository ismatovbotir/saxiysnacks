<?php

namespace Tests\Feature;

use App\Actions\PostStockDocument;
use App\Actions\SaveStockDocument;
use App\Enums\AdminPermission;
use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Livewire\Admin\ClientPicker;
use App\Livewire\Admin\DocumentLines;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\StockMovement;
use App\Models\TelegramClient;
use App\Models\User;
use App\Support\RolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithTelegram;
use Tests\TestCase;

class StockTest extends TestCase
{
    use InteractsWithTelegram, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTelegram();
        $this->actingAs(User::factory()->role(UserRole::Warehouse)->create(['locale' => 'en']));
    }

    private function balance(Product $product): int
    {
        return (int) $product->stockMovements()->sum('quantity');
    }

    public function test_a_receipt_invoice_moves_stock_only_once_posted_and_cancelling_takes_it_back(): void
    {
        $product = Product::factory()->create(['box_quantity' => 40]);

        $this->post(route('admin.stock.receipts.store'), [
            'document_date' => today()->toDateString(),
            'counterparty' => 'Production line 1',
            'lines' => [
                ['product_id' => $product->id, 'unit' => 'package', 'quantity' => 3, 'price' => '60 000'],
                ['product_id' => '', 'unit' => 'piece', 'quantity' => ''],
            ],
        ])->assertRedirect();

        $document = StockDocument::sole();
        $this->assertSame(['IN-00001', StockDocumentStatus::Draft, 180000], [$document->number, $document->status, $document->total]);
        $this->assertSame(0, $this->balance($product));

        $this->post(route('admin.stock.documents.post', $document))->assertSessionHas('status');
        $this->assertSame(120, $this->balance($product));
        $this->put(route('admin.stock.documents.update', $document), ['document_date' => today()->toDateString(), 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 1]]])
            ->assertSessionHasErrors('document');

        $this->get(route('admin.stock.documents.show', $document))->assertOk()->assertSee('IN-00001')->assertSee('Production line 1')->assertSee("180 000 so'm")->assertSee('Unpost');
        $this->get(route('admin.stock.receipts.index'))->assertOk()->assertSee('IN-00001')->assertSee("180 000 so'm");
        $this->get(route('admin.stock.movements', ['product' => $product->id]))->assertOk()->assertSee('IN-00001');

        $this->post(route('admin.stock.documents.unpost', $document))->assertSessionHas('status');
        $this->assertSame([0, StockDocumentStatus::Draft], [$this->balance($product), $document->fresh()->status]);

        $this->delete(route('admin.stock.documents.destroy', $document))->assertRedirect(route('admin.stock.receipts.index'));
        $this->assertDatabaseEmpty('stock_documents');
    }

    public function test_corrections_can_add_and_remove_but_not_below_zero(): void
    {
        $product = Product::factory()->create(['box_quantity' => 40]);
        $receipt = app(SaveStockDocument::class)->handle(StockDocumentType::Receipt, ['document_date' => today()->toDateString()], [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 50]]);
        app(PostStockDocument::class)->handle($receipt);

        $correction = ['document_date' => today()->toDateString(), 'counterparty' => 'Count 14.10', 'post' => 1];

        $this->post(route('admin.stock.corrections.store'), [...$correction, 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => -80]]])
            ->assertSessionHasErrors('document');
        $this->assertSame(50, $this->balance($product));

        $this->post(route('admin.stock.corrections.store'), [...$correction, 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => -5], ['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 2]]])
            ->assertSessionHas('status');
        $this->assertSame(47, $this->balance($product));

        $this->post(route('admin.stock.corrections.store'), [...$correction, 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 0]]])
            ->assertSessionHasErrors('lines.0.quantity');
    }

    public function test_drafts_can_be_edited_and_deleted_but_posted_documents_cannot(): void
    {
        $product = Product::factory()->create();
        $document = app(SaveStockDocument::class)->handle(StockDocumentType::Receipt, ['document_date' => today()->toDateString()], [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 5]]);

        $this->put(route('admin.stock.documents.update', $document), ['document_date' => today()->toDateString(), 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 9]]])
            ->assertRedirect(route('admin.stock.documents.show', $document));
        $this->assertSame(9, $document->lines()->sole()->quantity);

        app(PostStockDocument::class)->handle($document);

        $this->delete(route('admin.stock.documents.destroy', $document))->assertSessionHas('error');
        $this->put(route('admin.stock.documents.update', $document), ['document_date' => today()->toDateString(), 'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 1]]])
            ->assertSessionHasErrors('document');
        $this->assertSame(9, $this->balance($product));
    }

    public function test_the_document_pages_render(): void
    {
        foreach (StockDocumentType::cases() as $type) {
            $this->get(route('admin.stock.'.$type->slug().'.index'))->assertOk()->assertSee($type->pluralLabel());
            $this->get(route('admin.stock.'.$type->slug().'.create'))->assertOk()->assertSeeLivewire(DocumentLines::class);
        }

        $this->get(route('admin.stock.index'))->assertOk()->assertSee(route('admin.stock.corrections.create'), false);
        $this->get(route('admin.dashboard'))->assertSee(route('admin.stock.receipts.index'))->assertSee(route('admin.stock.sales.index'));
    }

    public function test_shipping_an_order_takes_pieces_out_once_and_cancelling_puts_them_back(): void
    {
        $product = Product::factory()->create(['box_quantity' => 40]);
        StockMovement::factory()->for($product)->create(['quantity' => 400]);
        $order = Order::factory()->create(['price_type' => ClientType::Reseller, 'status' => OrderStatus::Confirmed]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Kurt', 'weight_grams' => 30, 'box_quantity' => 40, 'quantity' => 3, 'unit_price' => 100000, 'line_total' => 300000]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'shipped']);
        $this->patch(route('admin.orders.status', $order), ['status' => 'completed']);
        $this->assertSame(280, $this->balance($product));
        $this->assertDatabaseHas('stock_movements', ['order_id' => $order->id, 'type' => StockMovementType::Shipment, 'quantity' => -120]);
        $this->assertSame([StockDocumentType::Sale, StockDocumentStatus::Posted, 3, 300000], [$order->saleDocument->type, $order->saleDocument->status, $order->saleDocument->lines->sole()->quantity, $order->saleDocument->total]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertSame(280, $this->balance($product));

        $this->actingAs(User::factory()->create());
        $this->patch(route('admin.orders.status', $order), ['status' => 'cancelled', 'override' => 1]);
        $this->assertSame(400, $this->balance($product));
        $this->assertSame(StockDocumentStatus::Cancelled, $order->fresh()->saleDocument->status);

        $this->post(route('admin.stock.documents.unpost', $order->fresh()->saleDocument))->assertSessionHasErrors('document');

        $this->patch(route('admin.orders.status', $order), ['status' => 'shipped', 'override' => 1]);
        $this->assertSame(280, $this->balance($product));
        $this->assertSame(2, StockDocument::count());
    }

    public function test_consumer_orders_take_out_single_pieces(): void
    {
        $product = Product::factory()->create(['box_quantity' => 40]);
        $order = Order::factory()->create(['price_type' => ClientType::Consumer, 'status' => OrderStatus::Confirmed]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Kurt', 'weight_grams' => 30, 'box_quantity' => 40, 'quantity' => 7, 'unit_price' => 3000, 'line_total' => 21000]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'shipped'])->assertRedirect();

        $this->assertSame(-7, $this->balance($product));
    }

    public function test_the_warehouse_cannot_open_finance(): void
    {
        $this->get(route('admin.finance.report'))->assertForbidden();
        $this->get(route('admin.finance.payments.index'))->assertForbidden();
    }

    public function test_the_lines_editor_adds_products_from_the_search(): void
    {
        $kurt = Product::factory()->create(['name' => ['uz' => 'Qurt', 'ru' => 'Курт', 'en' => 'Kurt'], 'box_quantity' => 40]);
        Product::factory()->create(['name' => ['uz' => 'Yong\'oq', 'ru' => 'Орех', 'en' => 'Nuts']]);

        Livewire::test(DocumentLines::class, ['type' => StockDocumentType::Receipt])
            ->set('search', 'qur')
            ->assertSee('Kurt')
            ->assertDontSee('Nuts')
            ->call('addFirstMatch')
            ->assertSet('lines.0.product_id', (string) $kurt->id)
            ->assertSet('lines.0.unit', 'package')
            ->call('addProduct', $kurt->id)
            ->assertSet('lines.0.quantity', '2')
            ->assertSee('80')
            ->call('removeLine', 0)
            ->assertSet('lines', []);
    }

    public function test_access_follows_the_document_type(): void
    {
        $document = app(SaveStockDocument::class)->handle(StockDocumentType::Receipt, ['document_date' => today()->toDateString()], [['product_id' => Product::factory()->create()->id, 'unit' => 'piece', 'quantity' => 1]]);
        app(RolePermissions::class)->update(UserRole::Warehouse, [AdminPermission::Corrections]);

        $this->get(route('admin.stock.receipts.index'))->assertForbidden();
        $this->get(route('admin.stock.documents.show', $document))->assertForbidden();
        $this->get(route('admin.stock.corrections.index'))->assertOk();
    }

    public function test_orders_whose_pieces_cannot_be_counted_are_not_shipped(): void
    {
        $order = Order::factory()->create(['price_type' => ClientType::Reseller, 'status' => OrderStatus::Confirmed]);
        $order->items()->create(['product_id' => Product::factory()->create()->id, 'product_name' => 'Kurt', 'weight_grams' => 30, 'box_quantity' => null, 'quantity' => 3, 'unit_price' => 100000, 'line_total' => 300000]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'shipped'])->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertDatabaseEmpty('stock_movements');
    }

    public function test_only_the_owner_can_skip_the_order_flow(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::New]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'completed', 'override' => 1])->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::New, $order->fresh()->status);
    }

    public function test_products_with_history_are_hidden_not_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        StockMovement::factory()->for($product)->create();

        $this->delete(route('admin.products.destroy', $product))->assertSessionHas('error');
        $this->assertModelExists($product);
    }

    public function test_the_customer_of_a_sales_invoice_is_picked_from_the_client_list(): void
    {
        $aziz = TelegramClient::factory()->reseller()->create(['company_name' => 'Aziz Market', 'phone' => '+998 90 123 45 67']);
        TelegramClient::factory()->create(['company_name' => 'Other Shop', 'phone' => '+998 99 000 00 00']);

        Livewire::test(ClientPicker::class, ['label' => 'Customer'])
            ->call('$set', 'open', true)
            ->assertSee('Aziz Market')
            ->set('search', '90123')
            ->assertSee('Aziz Market')
            ->assertDontSee('Other Shop')
            ->call('choose', $aziz->id)
            ->assertSet('name', 'Aziz Market')
            ->assertSet('clientId', $aziz->id)
            ->assertSet('open', false)
            ->set('name', 'Someone else')
            ->assertSet('clientId', null);

        $product = Product::factory()->create();
        StockMovement::factory()->for($product)->create(['quantity' => 10]);

        $this->post(route('admin.stock.sales.store'), [
            'document_date' => today()->toDateString(),
            'counterparty' => 'Aziz Market',
            'telegram_client_id' => $aziz->id,
            'lines' => [['product_id' => $product->id, 'unit' => 'piece', 'quantity' => 2, 'price' => 3000]],
        ])->assertRedirect();

        $document = StockDocument::sole();
        $this->assertTrue($document->client->is($aziz));
        $this->get(route('admin.stock.documents.show', $document))->assertOk()->assertSeeLivewire(ClientPicker::class);
    }
}
