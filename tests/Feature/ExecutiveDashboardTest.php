<?php

namespace Tests\Feature;

use App\Models\Godown;
use App\Models\ReferenceMaster;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ErpFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExecutiveDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ReferenceMaster $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AccessControlSeeder::class, ErpFoundationSeeder::class]);
        $this->admin = User::where('username', 'superadmin')->firstOrFail();
        $this->year = ReferenceMaster::ofType('financial_year')->firstOrFail();
    }

    public function test_dashboard_renders_a_lazy_ajax_shell(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('id=\'dashboard-kpis\'', false)->assertSee('data-chart=\'sales-purchase\'', false)
            ->assertSee('data-lazy-tab=\'finance\'', false)->assertSee('dashboard.js', false);
    }

    public function test_super_admin_consolidated_kpis_include_all_shops(): void
    {
        $one = Shop::create(['name' => 'One', 'code' => 'ONE', 'is_active' => true]);
        $two = Shop::create(['name' => 'Two', 'code' => 'TWO', 'is_active' => true]);
        foreach ([[$one, 1250], [$two, 2750]] as [$shop,$amount]) {
            DB::table('commercial_documents')->insert([
                'shop_id' => $shop->id, 'financial_year_id' => $this->year->id, 'type' => 'sales_invoice', 'number' => 'INV-'.$shop->id,
                'document_date' => now()->toDateString(), 'status' => 'posted', 'subtotal' => $amount, 'total_amount' => $amount,
                'balance_amount' => $amount, 'created_by' => $this->admin->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $response = $this->withSession(['all_shops_context' => true, 'active_shop_id' => null, 'active_godown_id' => null, 'active_financial_year_id' => $this->year->id])
            ->actingAs($this->admin)->getJson(route('admin.dashboard.kpis', ['preset' => 'today']))->assertOk();
        $sales = collect($response->json('data.cards'))->firstWhere('key', 'sales');
        $this->assertSame(4000.0, (float) $sales['value']);
        $this->assertSame('company', $response->json('meta.mode'));
    }

    public function test_custom_date_validation_and_unknown_charts_are_rejected(): void
    {
        $this->actingAs($this->admin)->getJson(route('admin.dashboard.kpis', ['preset' => 'custom', 'date_from' => '2026-07-18']))->assertUnprocessable();
        $this->actingAs($this->admin)->getJson('/admin/dashboard/charts/not-real?preset=today')->assertNotFound();
    }

    public function test_godown_mode_aggregates_its_shops_without_leaking_other_godowns(): void
    {
        $one = Shop::create(['name' => 'One', 'code' => 'G-ONE', 'is_active' => true]);
        $two = Shop::create(['name' => 'Two', 'code' => 'G-TWO', 'is_active' => true]);
        $godown = Godown::create(['shop_id' => $one->id, 'name' => 'Shared', 'code' => 'SHARED', 'is_active' => true]);
        $godown->shops()->sync([$one->id, $two->id]);
        foreach ([[$one, 100], [$two, 200]] as [$shop,$amount]) {
            DB::table('commercial_documents')->insert(['shop_id' => $shop->id, 'godown_id' => $godown->id, 'financial_year_id' => $this->year->id, 'type' => 'sales_invoice', 'number' => 'G-'.$shop->id, 'document_date' => now()->toDateString(), 'status' => 'posted', 'total_amount' => $amount, 'created_by' => $this->admin->id, 'created_at' => now(), 'updated_at' => now()]);
        }
        $response = $this->withSession(['active_shop_id' => $one->id, 'active_godown_id' => $godown->id, 'active_financial_year_id' => $this->year->id])->actingAs($this->admin)->getJson(route('admin.dashboard.kpis', ['preset' => 'today']))->assertOk();
        $this->assertSame('godown', $response->json('meta.mode'));
        $this->assertSame(300.0, (float) collect($response->json('data.cards'))->firstWhere('key', 'sales')['value']);
    }

    public function test_dashboard_only_user_does_not_receive_unassigned_quick_actions(): void
    {
        $role = Role::create(['name' => 'Dashboard Viewer', 'slug' => 'dashboard-viewer', 'is_active' => true]);
        $role->permissions()->attach(DB::table('permissions')->where('code', 'dashboard.view')->value('id'));
        $user = User::create(['role_id' => $role->id, 'name' => 'Viewer', 'username' => 'dash-viewer', 'email' => 'dash@example.test', 'password' => 'password123', 'is_active' => true]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertSee('No quick actions are assigned to your role.')->assertDontSee('Create customer invoice');
        $this->actingAs($user)->getJson(route('admin.dashboard.chart', ['chart' => 'profit', 'preset' => 'today']))->assertForbidden();
    }

    public function test_closed_financial_year_is_reported_as_an_alert(): void
    {
        $meta = $this->year->metadata;
        $meta['is_closed'] = true;
        $this->year->update(['metadata' => $meta]);
        $this->withSession(['active_financial_year_id' => $this->year->id])->actingAs($this->admin)
            ->getJson(route('admin.dashboard.alerts', ['preset' => 'financial_year']))->assertOk()->assertJsonFragment(['key' => 'closed_year', 'title' => 'Financial year closed']);
    }

    public function test_every_lazy_dashboard_component_returns_the_standard_contract(): void
    {
        $this->withSession(['active_financial_year_id' => $this->year->id])->actingAs($this->admin);
        foreach (['sales-purchase', 'collections', 'profit', 'expenses', 'shop-comparison', 'godown-comparison', 'payment-methods', 'receivable-ageing', 'payable-ageing'] as $chart) {
            $this->getJson(route('admin.dashboard.chart', ['chart' => $chart, 'preset' => 'today']))->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['chart'], 'meta' => ['mode', 'financial_year_id', 'date_from', 'date_to']]);
        }
        foreach (['products', 'customers', 'suppliers'] as $type) {
            $this->getJson(route('admin.dashboard.top', ['type' => $type, 'preset' => 'today']))->assertOk()->assertJsonPath('success', true);
        }
        foreach (['sales', 'purchases', 'inventory', 'finance', 'collections', 'deliveries', 'alerts', 'activity'] as $tab) {
            $this->getJson(route('admin.dashboard.tab', ['tab' => $tab, 'preset' => 'today']))->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.tab', $tab);
        }
        $this->getJson(route('admin.dashboard.activity', ['preset' => 'today']))->assertOk()->assertJsonPath('success',true);
    }
}
