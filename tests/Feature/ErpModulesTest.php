<?php

namespace Tests\Feature;

use App\Models\CommercialDocument;
use App\Models\Delivery;
use App\Models\Godown;
use App\Models\InventoryBalance;
use App\Models\LedgerAccount;
use App\Models\Party;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Shop;
use App\Models\User;
use App\Models\Voucher;
use App\Services\ReportService;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ErpFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ErpModulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Shop $shop;

    private Godown $godown;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AccessControlSeeder::class, ErpFoundationSeeder::class]);
        $this->admin = User::where('username', 'superadmin')->firstOrFail();
        $this->shop = Shop::create(['name' => 'Main Shop', 'code' => 'MAIN', 'is_active' => true]);
        $this->godown = Godown::create(['shop_id' => $this->shop->id, 'name' => 'Main Godown', 'code' => 'MAIN-G', 'is_active' => true]);
        $this->actingAs($this->admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_reference_master_crud_is_ajax_and_module_scoped(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/units', [
            'name' => 'Box', 'code' => 'BOX', 'decimal_places' => 0, 'base_unit' => 'Piece', 'conversion_value' => 1, 'status' => 'Active',
        ])->assertCreated()->assertJsonPath('status', true);

        $id = $response->json('data.id');
        $this->assertDatabaseHas('reference_masters', ['id' => $id, 'type' => 'unit', 'code' => 'BOX']);
        $this->actingAs($this->admin)->putJson('/admin/units/'.$id, [
            'name' => 'Carton', 'code' => 'BOX', 'decimal_places' => 0, 'base_unit' => 'Piece', 'conversion_value' => 1, 'status' => 'Active',
        ])->assertOk();
    }

    public function test_purchase_and_sale_documents_update_stock_atomically(): void
    {
        $supplier = Party::create(['shop_id' => $this->shop->id, 'type' => 'supplier', 'code' => 'SUP-1', 'name' => 'Supplier', 'balance_type' => 'payable', 'is_active' => true]);
        $customer = Party::create(['shop_id' => $this->shop->id, 'type' => 'customer', 'code' => 'CUS-1', 'name' => 'Customer', 'balance_type' => 'receivable', 'is_active' => true]);
        $tax = ReferenceMaster::where('type', 'tax_rate')->where('code', 'GST5')->firstOrFail();
        $product = Product::create(['name' => 'ERP Product', 'slug' => 'erp-product', 'sku' => 'ERP-1', 'purchase_price' => 100, 'sale_price' => 150, 'tax_rate_id' => $tax->id, 'is_active' => true]);

        $purchase = $this->actingAs($this->admin)->postJson('/admin/documents/purchase-bills', [
            'party_id' => $supplier->id, 'document_date' => now()->toDateString(), 'status' => 'posted',
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'rate' => 100, 'discount_amount' => 0]],
        ])->assertCreated()->assertJsonPath('data.total_amount', '1050.00');

        $this->assertSame('10.000', InventoryBalance::firstOrFail()->quantity);

        $sale = $this->actingAs($this->admin)->postJson('/admin/documents/sales-invoices', [
            'party_id' => $customer->id, 'document_date' => now()->toDateString(), 'status' => 'posted',
            'items' => [['product_id' => $product->id, 'quantity' => 4, 'rate' => 150, 'discount_amount' => 0]],
        ])->assertCreated()->assertJsonPath('data.total_amount', '630.00');

        $this->assertSame('6.000', InventoryBalance::firstOrFail()->fresh()->quantity);
        $this->assertDatabaseCount('inventory_movements', 2);

        $saleId = $sale->json('data.id');
        $this->actingAs($this->admin)->postJson('/admin/accounts/payments', [
            'party_id' => $customer->id, 'commercial_document_id' => $saleId, 'type' => 'customer_collection',
            'payment_date' => now()->toDateString(), 'amount' => 200,
        ])->assertCreated();
        $this->assertDatabaseHas('commercial_documents', ['id' => $saleId, 'paid_amount' => 200, 'balance_amount' => 430]);
        $this->assertSame(3, Voucher::whereNotNull('source_type')->count());
        $this->assertDatabaseHas('vouchers', ['source_type' => 'commercial_document', 'source_id' => $saleId, 'total_debit' => 630, 'total_credit' => 630]);

        $this->actingAs($this->admin)->getJson('/admin/parties/customers/'.$customer->id)
            ->assertOk()->assertJsonPath('data.summary.outstanding', 430);
        $this->actingAs($this->admin)->get('/admin/parties/customers/'.$customer->id.'/transactions', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonCount(2, 'data');

        $this->actingAs($this->admin)
            ->get('/admin/dashboard?period=30')
            ->assertOk()
            ->assertSee('Top-level analysis')
            ->assertSee('Product-level analysis')
            ->assertSee('ERP Product')
            ->assertSee('id="financial-trend-chart"', false)
            ->assertSee('id="top-products-chart"', false)
            ->assertSee('id="stock-category-chart"', false)
            ->assertSee('id="godown-stock-chart"', false);
    }

    public function test_voucher_requires_balanced_debit_and_credit(): void
    {
        $cash = LedgerAccount::where('code', 'CASH')->firstOrFail();
        $sales = LedgerAccount::where('code', 'SALES')->firstOrFail();
        $payload = ['type' => 'journal', 'voucher_date' => now()->toDateString(), 'lines' => [
            ['ledger_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['ledger_account_id' => $sales->id, 'debit' => 0, 'credit' => 500],
        ]];
        $this->actingAs($this->admin)->postJson('/admin/accounts/vouchers', $payload)->assertCreated();
        $payload['lines'][1]['credit'] = 400;
        $this->actingAs($this->admin)->postJson('/admin/accounts/vouchers', $payload)->assertUnprocessable()->assertJsonValidationErrors('lines');
    }

    public function test_payments_datatable_never_orders_by_the_virtual_serial_column(): void
    {
        $query = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'number', 'name' => 'number', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'payment_date', 'name' => 'payment_date', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [['column' => 0, 'dir' => 'desc']],
            'search' => ['value' => '', 'regex' => 'false'],
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/accounts/payments?'.$query, ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data'])
            ->assertJsonMissingPath('error');
    }

    public function test_reports_and_all_registered_module_pages_render(): void
    {
        foreach (array_keys(ReportService::REPORTS) as $report) {
            $this->actingAs($this->admin)->get('/admin/reports/'.$report)->assertOk();
            $this->actingAs($this->admin)->get('/admin/reports/'.$report, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonStructure(['data']);
        }
        foreach (array_keys(config('erp_modules.reference')) as $module) {
            $moduleConfig = config('erp_modules.reference.'.$module);
            $record = ReferenceMaster::firstOrCreate(
                ['type' => $moduleConfig['type'], 'code' => 'PAGE-'.strtoupper($module)],
                ['name' => 'Page rendering record', 'is_active' => true],
            );
            $response = $this->actingAs($this->admin)->get(route('admin.'.$module.'.index'))
                ->assertOk()
                ->assertViewIs('backend.'.$module.'.index')
                ->assertSee('module-filter-accordion', false)
                ->assertSee('initializeDataTable', false)
                ->assertDontSee('cdn.datatables.net', false)
                ->assertDontSee('code.jquery.com', false);

            $dataTableQuery = http_build_query([
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [[
                    'data' => 'DT_RowIndex',
                    'name' => 'DT_RowIndex',
                    'searchable' => 'false',
                    'orderable' => 'false',
                    'search' => ['value' => '', 'regex' => 'false'],
                ]],
                'search' => ['value' => '', 'regex' => 'false'],
            ]);
            $this->actingAs($this->admin)
                ->get(route('admin.'.$module.'.index').'?'.$dataTableQuery, ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()
                ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

            $this->actingAs($this->admin)->get(route('admin.'.$module.'.create'))
                ->assertOk()
                ->assertViewIs('backend.'.$module.'.create')
                ->assertSee('id="form-validate"', false)
                ->assertSee("$('#form-validate').validate", false)
                ->assertSee('name="_token"', false);

            $this->actingAs($this->admin)->get(route('admin.'.$module.'.edit', $record->id))
                ->assertOk()
                ->assertViewIs('backend.'.$module.'.edit')
                ->assertViewHas('recordId', (string) $record->id)
                ->assertSee('id="form-validate"', false)
                ->assertSee("$('#form-validate').validate", false)
                ->assertSee('name="_token"', false);

            if ($module === 'categories') {
                $response->assertSee('backend/assets/vendor/jquery/jquery.min.js', false)
                    ->assertSee('backend/assets/vendor/datatables/jquery.dataTables.min.js', false);
            }
        }
        $this->actingAs($this->admin)->get('/admin/parties/customers')->assertOk()->assertSee('party-workspace', false)->assertSee('party-transactions-table', false);
        foreach (array_keys(config('erp_modules.documents')) as $module) {
            $this->actingAs($this->admin)->get('/admin/documents/'.$module)
                ->assertOk()
                ->assertViewIs('backend.documents.'.$module.'.index')
                ->assertSee('name="_token"', false)
                ->assertDontSee('cdn.datatables.net', false)
                ->assertDontSee('code.jquery.com', false);
        }
        $this->actingAs($this->admin)->get('/admin/inventory/stock')->assertOk();
        $this->actingAs($this->admin)->get('/admin/deliveries')->assertOk();
        $this->actingAs($this->admin)->get('/admin/notifications')->assertOk();
    }

    public function test_product_opening_stock_is_posted_once_to_active_godown(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/products', [
            'name' => 'Opening Stock Product',
            'sku' => 'OPEN-001',
            'purchase_price' => 75,
            'sale_price' => 100,
            'opening_stock' => 12.5,
            'reorder_level' => 3,
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('status', true);

        $productId = $response->json('data.id');
        $this->assertDatabaseHas('inventory_balances', [
            'shop_id' => $this->shop->id,
            'godown_id' => $this->godown->id,
            'product_id' => $productId,
            'quantity' => 12.5,
        ]);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => $productId, 'type' => 'opening_stock', 'quantity' => 12.5]);

        $this->actingAs($this->admin)->putJson('/admin/products/'.$productId, [
            'name' => 'Opening Stock Product',
            'opening_stock' => 99,
        ])->assertUnprocessable()->assertJsonValidationErrors('opening_stock');
    }

    public function test_operational_alert_refresh_builds_low_stock_expiry_payment_and_delivery_alerts(): void
    {
        $customer = Party::create(['shop_id' => $this->shop->id, 'type' => 'customer', 'code' => 'ALERT-C', 'name' => 'Alert Customer', 'balance_type' => 'receivable', 'is_active' => true]);
        $product = Product::create(['name' => 'Alert Product', 'slug' => 'alert-product', 'reorder_level' => 5, 'is_active' => true]);
        InventoryBalance::create(['shop_id' => $this->shop->id, 'godown_id' => $this->godown->id, 'product_id' => $product->id, 'batch_number' => 'B-1', 'expiry_date' => now()->addDays(10), 'quantity' => 2, 'average_cost' => 10]);
        $document = CommercialDocument::create([
            'shop_id' => $this->shop->id, 'godown_id' => $this->godown->id, 'party_id' => $customer->id,
            'type' => 'sales_invoice', 'number' => 'ALERT-INV-1', 'document_date' => now()->subDays(20),
            'due_date' => now()->subDay(), 'status' => 'posted', 'total_amount' => 500, 'balance_amount' => 500,
            'created_by' => $this->admin->id,
        ]);
        Delivery::create(['shop_id' => $this->shop->id, 'commercial_document_id' => $document->id, 'status' => 'assigned', 'scheduled_at' => now()]);

        $this->actingAs($this->admin)->postJson('/admin/notifications/generate')->assertOk()->assertJsonPath('status', true);
        foreach (['low_stock', 'expiry', 'payment_reminder', 'pending_delivery'] as $type) {
            $this->assertDatabaseHas('user_notifications', ['user_id' => $this->admin->id, 'shop_id' => $this->shop->id, 'type' => $type]);
        }
    }

    public function test_maintenance_backup_and_controlled_csv_import_are_operational(): void
    {
        $this->actingAs($this->admin)->get('/admin/maintenance')->assertOk();
        $response = $this->actingAs($this->admin)->postJson('/admin/maintenance/backups')->assertCreated()->assertJsonPath('status', true);
        $backup = storage_path('app/private/erp-backups/'.$response->json('data.name'));
        $this->assertFileExists($backup);

        $csv = UploadedFile::fake()->createWithContent('reference-masters.csv', "type,code,name,description,percentage,is_active\nunit,CRT,Carton,Carton unit,,1\n");
        $this->actingAs($this->admin)->post('/admin/maintenance/import', ['dataset' => 'reference-masters', 'file' => $csv], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.count', 1);
        $this->assertDatabaseHas('reference_masters', ['type' => 'unit', 'code' => 'CRT', 'name' => 'Carton']);
        File::delete($backup);
    }
}
