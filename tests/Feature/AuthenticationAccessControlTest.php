<?php

namespace Tests\Feature;

use App\Models\Godown;
use App\Models\Permission;
use App\Models\ReferenceMaster;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
    }

    public function test_guest_is_redirected_to_common_login(): void
    {
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }

    public function test_login_screen_includes_ajax_auth_affordances(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('auth.css', false)
            ->assertSee('id="login-form"', false)
            ->assertSee('data-auth-password-toggle="#password"', false)
            ->assertSee('data-loading-label="Signing in..."', false)
            ->assertSee('auth-login.js', false)
            ->assertSee('Version', false)
            ->assertSee('Access is monitored', false);
    }

    public function test_auth_security_state_screens_render_for_future_policies(): void
    {
        foreach ([
            '/admin/session-expired' => 'Your secure session expired',
            '/admin/account-locked' => 'This account needs administrator help',
            '/admin/otp-verification' => 'Verify your one-time code',
            '/admin/two-factor-verification' => 'Complete two-factor verification',
            '/admin/device-approval' => 'Approve this device',
        ] as $url => $copy) {
            $this->get($url)
                ->assertOk()
                ->assertSee($copy)
                ->assertSee('Back to sign in');
        }
    }

    public function test_party_workspace_renders_drawer_ready_actions(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.parties.index', 'customers'))
            ->assertOk()
            ->assertSee('data-payment-url=', false)
            ->assertSee('data-document-url=', false)
            ->assertSee('<th>Actions</th>', false)
            ->assertSee('party-detail-modal', false);
    }

    public function test_product_workspace_renders_stock_aware_item_master(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('data-stock-url=', false)
            ->assertSee('data-transfer-url=', false)
            ->assertSee('Low Stock')
            ->assertSee('Without Image')
            ->assertSee('<th>Item Code</th>', false)
            ->assertSee('<th>Stock</th>', false);
    }

    public function test_pos_billing_workspace_renders_fast_shortcuts(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.documents.index', 'pos-billing'))
            ->assertOk()
            ->assertSee('erp-billing-shortcuts', false)
            ->assertSee('data-document-shortcut="hold"', false)
            ->assertSee('data-document-shortcut="save-print"', false)
            ->assertSee('data-document-shortcut="save-pay"', false)
            ->assertSee('name="idempotency_key"', false)
            ->assertSee('data-payment-url=', false);
    }

    public function test_user_can_login_with_username_and_permission_context_is_loaded(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->post('/admin/login', ['login' => 'admin@gmail.com', 'password' => '12345678']);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, session('user_id'));
        $this->assertSame($user->role_id, session('role_id'));
        $this->assertSame(['*'], session('permitted_shop_ids'));
        $this->assertSame(['*'], session('permitted_financial_year_ids'));
        $this->assertSame(['*'], session('permitted_actions'));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'login.success']);
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'event' => 'login.success']);
    }

    public function test_user_can_login_through_ajax_and_receives_default_context(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();

        $this->postJson('/admin/login', [
            'login' => 'admin@gmail.com',
            'password' => '12345678',
        ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.is_super_admin', true)
            ->assertJsonPath('data.redirect', route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $role = Role::create(['name' => 'Clerk', 'slug' => 'clerk', 'is_active' => true]);
        User::create(['role_id' => $role->id, 'name' => 'Inactive', 'username' => 'inactive', 'email' => 'inactive@example.test', 'password' => 'password123', 'is_active' => false]);

        $this->post('/admin/login', ['login' => 'inactive', 'password' => 'password123'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['event' => 'login.blocked']);
    }

    public function test_normal_user_with_multiple_locations_selects_context_before_dashboard(): void
    {
        $role = Role::create(['name' => 'Multi Location', 'slug' => 'multi-location', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Multi User', 'username' => 'multi.user', 'email' => 'multi@example.test', 'password' => 'password123', 'is_active' => true]);
        $shopOne = Shop::create(['name' => 'Shop One', 'code' => 'SHOP-ONE', 'is_active' => true]);
        $shopTwo = Shop::create(['name' => 'Shop Two', 'code' => 'SHOP-TWO', 'is_active' => true]);
        $godownOne = Godown::create(['shop_id' => $shopOne->id, 'name' => 'Godown One', 'code' => 'GODOWN-ONE', 'is_active' => true]);
        $godownTwo = Godown::create(['shop_id' => $shopTwo->id, 'name' => 'Godown Two', 'code' => 'GODOWN-TWO', 'is_active' => true]);
        $year = ReferenceMaster::create(['type' => 'financial_year', 'code' => 'FY-LOGIN', 'name' => 'Login Year', 'is_active' => true]);
        $user->shops()->attach([$shopOne->id, $shopTwo->id], ['is_active' => true]);
        $user->godowns()->attach([$godownOne->id, $godownTwo->id], ['is_active' => true]);
        $user->financialYears()->attach($year->id, ['is_active' => true]);

        $this->postJson(route('admin.auth.login'), ['login' => $user->username, 'password' => 'password123'])
            ->assertOk()
            ->assertJsonPath('data.requires_location_selection', true)
            ->assertJsonPath('data.redirect', route('admin.auth.context.index'));

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.auth.context.index'));
        $this->get(route('admin.auth.context.index'))->assertOk()->assertSee('Choose where you are working');
        $this->getJson(route('admin.auth.context.shops', ['godown_id' => $godownOne->id]))
            ->assertOk()->assertJsonPath('data.shops.0.id', $shopOne->id);

        $this->postJson(route('admin.auth.context.store'), [
            'godown_id' => $godownOne->id,
            'shop_id' => $shopOne->id,
            'financial_year_id' => $year->id,
        ])->assertOk()->assertJsonPath('data.redirect', route('admin.dashboard'));

        $this->assertFalse(session('auth_location_selection_required'));
        $this->assertSame($shopOne->id, session('active_shop_id'));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'login.context_selected']);
    }

    public function test_single_assigned_context_is_selected_automatically(): void
    {
        $role = Role::create(['name' => 'Single Location', 'slug' => 'single-location', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Single User', 'username' => 'single.user', 'email' => 'single@example.test', 'password' => 'password123', 'is_active' => true]);
        $shop = Shop::create(['name' => 'Only Shop', 'code' => 'ONLY-SHOP', 'is_active' => true]);
        $godown = Godown::create(['shop_id' => $shop->id, 'name' => 'Only Godown', 'code' => 'ONLY-GODOWN', 'is_active' => true]);
        $year = ReferenceMaster::create(['type' => 'financial_year', 'code' => 'FY-ONLY', 'name' => 'Only Year', 'is_active' => true]);
        $user->shops()->attach($shop->id, ['is_active' => true, 'is_default' => true]);
        $user->godowns()->attach($godown->id, ['is_active' => true, 'is_default' => true]);
        $user->financialYears()->attach($year->id, ['is_active' => true, 'is_default' => true]);

        $this->postJson(route('admin.auth.login'), ['login' => $user->email, 'password' => 'password123'])
            ->assertOk()
            ->assertJsonPath('data.requires_location_selection', false)
            ->assertJsonPath('data.redirect', route('admin.dashboard'));

        $this->assertSame([$year->id], session('permitted_financial_year_ids'));
        $this->assertSame($shop->id, session('active_shop_id'));
        $this->assertSame($godown->id, session('active_godown_id'));
    }

    public function test_login_throttle_returns_locked_response_and_optional_captcha_is_enforced(): void
    {
        config()->set('erp_auth.throttle.max_attempts', 1);
        $this->postJson(route('admin.auth.login'), ['login' => 'rate-limit-user', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson(route('admin.auth.login'), ['login' => 'rate-limit-user', 'password' => 'wrong'])
            ->assertStatus(429)->assertJsonPath('error_code', 'LOGIN_THROTTLED');

        config()->set('erp_auth.captcha.enabled', true);
        $this->get(route('admin.auth.index'))->assertOk()->assertSee('Security check:');
        $answer = session('auth_captcha_answer');
        $this->postJson(route('admin.auth.login'), ['login' => 'admin@gmail.com', 'password' => '12345678'])
            ->assertUnprocessable()->assertJsonValidationErrors('captcha');
        $this->postJson(route('admin.auth.login'), ['login' => 'admin@gmail.com', 'password' => '12345678', 'captcha' => $answer])
            ->assertOk();
    }

    public function test_role_without_product_permission_is_forbidden(): void
    {
        $role = Role::create(['name' => 'No Products', 'slug' => 'no-products', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Staff', 'username' => 'staff', 'email' => 'staff@example.test', 'password' => 'password123', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/products')->assertForbidden();
    }

    public function test_deactivated_authenticated_user_is_logged_out_on_next_request(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_role_with_product_view_permission_can_open_module(): void
    {
        $role = Role::create(['name' => 'Viewer', 'slug' => 'viewer', 'is_active' => true]);
        $role->permissions()->attach(Permission::where('code', 'products.view')->firstOrFail());
        $user = User::create(['role_id' => $role->id, 'name' => 'Viewer', 'username' => 'viewer', 'email' => 'viewer@example.test', 'password' => 'password123', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/products')->assertOk()->assertDontSee('Add Product');
    }

    public function test_user_permission_override_can_allow_or_deny_role_access_immediately(): void
    {
        $permission = Permission::where('code', 'products.view')->firstOrFail();
        $role = Role::create(['name' => 'Override Role', 'slug' => 'override-role', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Override User', 'username' => 'override', 'email' => 'override@example.test', 'password' => 'password123', 'is_active' => true]);

        $user->permissions()->attach($permission, ['allowed' => true]);
        $this->actingAs($user)->get('/admin/products')->assertOk();

        $role->permissions()->attach($permission);
        $user->permissions()->updateExistingPivot($permission->id, ['allowed' => false]);
        $this->actingAs($user->fresh())->get('/admin/products')->assertForbidden();
    }

    public function test_shop_assignment_helpers_restrict_normal_users_and_bypass_super_admin(): void
    {
        $first = Shop::create(['name' => 'First', 'code' => 'FIRST', 'is_active' => true]);
        $second = Shop::create(['name' => 'Second', 'code' => 'SECOND', 'is_active' => true]);
        $role = Role::create(['name' => 'Shop User', 'slug' => 'shop-user', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Shop User', 'username' => 'shopuser', 'email' => 'shop@example.test', 'password' => Hash::make('password123'), 'is_active' => true]);
        $user->shops()->attach($first);

        $this->assertTrue($user->canAccessShop($first->id));
        $this->assertFalse($user->canAccessShop($second->id));
        $this->assertTrue(User::where('username', 'superadmin')->firstOrFail()->canAccessShop($second->id));
    }

    public function test_super_admin_access_control_blade_pages_render_successfully(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        foreach ([
            '/admin/access/users',
            '/admin/access/users/'.$admin->id.'/permissions',
            '/admin/access/roles',
            '/admin/access/locations',
            '/admin/access/activity-logs',
            '/admin/access/sessions',
            '/admin/settings',
            '/admin/dashboard',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_authenticated_header_renders_all_interactive_controls_and_notification_modal(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('id="topnav-hamburger-icon"', false)
            ->assertSee('id="scrollbar"', false)
            ->assertSee('id="two-column-menu"', false)
            ->assertSee('id="navbar-nav"', false)
            ->assertSee('id="sidebarParties"', false)
            ->assertSee('id="sidebarItems"', false)
            ->assertSee('id="sidebarSales"', false)
            ->assertSee('id="sidebarPurchases"', false)
            ->assertSee('id="sidebarInventory"', false)
            ->assertSee('id="sidebarReports"', false)
            ->assertSee('id="sidebarAdministration"', false)
            ->assertSeeInOrder([
                '<span>Dashboard</span>',
                '<span>Customer Workspace</span>',
                '<span>Supplier Workspace</span>',
                '<span>Inventory Workspace</span>',
                '<span>Accounting Workspace</span>',
                '<span>Delivery Workspace</span>',
                '<span>Parties</span>',
                '<span>Items</span>',
                '<span>Sale</span>',
                '<span>Purchase &amp; Expense</span>',
                '<span>Inventory</span>',
                '<span>Grow Your Business</span>',
                '<span>Cash, Bank &amp; Assets</span>',
                '<span>Accounting</span>',
                '<span>Delivery</span>',
                '<span>Reports</span>',
                '<span>Administration</span>',
            ], false)
            ->assertSee('id="search-options"', false)
            ->assertSee('id="global-search-results"', false)
            ->assertSee('header-search.js', false)
            ->assertDontSee('how to setup', false)
            ->assertSee('id="customizer-layout01"', false)
            ->assertSee('id="customizer-layout02"', false)
            ->assertSee('id="theme-settings-offcanvas"', false)
            ->assertDontSee('id="page-header-cart-dropdown"', false)
            ->assertSee('page-loader.js', false)
            ->assertSee('id="app-content"', false)
            ->assertSee('data-erp-main-content', false)
            ->assertSee('id="erp-content-skeleton"', false)
            ->assertSee('id="erp-right-drawer"', false)
            ->assertSee('id="erp-activity-timeline"', false)
            ->assertSee('id="erp-quick-action-bar"', false)
            ->assertSee('href="#app-content"', false)
            ->assertSee('id="erp-quick-create-toggle"', false)
            ->assertSee('id="erp-calculator-toggle"', false)
            ->assertSee('header-context.js', false)
            ->assertSee('id="erp-mobile-create"', false)
            ->assertSee('erp-prototype.css', false)
            ->assertSee('erp-ui.js', false)
            ->assertSee('id="erp-fullscreen-toggle"', false)
            ->assertSee('data-toggle="fullscreen"', false)
            ->assertSee('persistent-fullscreen.js', false)
            ->assertSee('light-dark-mode', false)
            ->assertSee('id="notificationDropdown"', false)
            ->assertSee('id="page-header-user-dropdown"', false)
            ->assertSee('id="removeNotificationModal"', false)
            ->assertDontSee('id="delete-notification"', false);
    }

    public function test_super_admin_can_switch_shop_and_godown_context_without_logout(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $shop = Shop::create(['name' => 'Context Shop', 'code' => 'CTX', 'is_active' => true]);
        $godown = Godown::create(['shop_id' => $shop->id, 'name' => 'Context Godown', 'code' => 'CTX-G', 'is_active' => true]);

        $this->actingAs($admin)->postJson('/admin/location-context/shop', [
            'shop_id' => $shop->id,
        ])->assertOk()->assertJsonPath('status', true);

        $this->actingAs($admin)->postJson('/admin/location-context/godown', [
            'godown_id' => $godown->id,
        ])->assertOk()->assertJsonPath('status', true);

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($shop->id, session('active_shop_id'));
        $this->assertSame($godown->id, session('active_godown_id'));
    }

    public function test_normal_user_can_only_switch_to_assigned_shop_and_godown(): void
    {
        $role = Role::create(['name' => 'Context Operator', 'slug' => 'context-operator', 'is_active' => true]);
        $role->permissions()->attach(Permission::whereIn('code', ['shops.switch', 'godowns.switch'])->pluck('id'));
        $user = User::create(['role_id' => $role->id, 'name' => 'Context Operator', 'username' => 'context-operator', 'email' => 'context-operator@example.test', 'password' => 'password123', 'is_active' => true]);
        $allowedShop = Shop::create(['name' => 'Allowed Shop', 'code' => 'ALLOWED', 'is_active' => true]);
        $blockedShop = Shop::create(['name' => 'Blocked Shop', 'code' => 'BLOCKED', 'is_active' => true]);
        $allowedGodown = Godown::create(['shop_id' => $allowedShop->id, 'name' => 'Allowed Godown', 'code' => 'ALLOWED-G', 'is_active' => true]);
        $blockedGodown = Godown::create(['shop_id' => $blockedShop->id, 'name' => 'Blocked Godown', 'code' => 'BLOCKED-G', 'is_active' => true]);
        $user->shops()->attach($allowedShop, ['is_default' => true, 'is_active' => true]);
        $user->godowns()->attach($allowedGodown, ['is_default' => true, 'is_active' => true]);

        $this->actingAs($user)->postJson('/admin/location-context/shop', ['shop_id' => $allowedShop->id])
            ->assertOk()
            ->assertJsonPath('data.active_godown_id', $allowedGodown->id);

        $this->actingAs($user)->getJson('/admin/location-context/godowns?shop_id='.$allowedShop->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $allowedGodown->id])
            ->assertJsonMissing(['id' => $blockedGodown->id]);

        $this->actingAs($user)->postJson('/admin/location-context/shop', ['shop_id' => $blockedShop->id])
            ->assertForbidden();
    }

    public function test_selecting_a_godown_only_returns_related_assigned_shops(): void
    {
        $role = Role::create(['name' => 'Godown Context Operator', 'slug' => 'godown-context-operator', 'is_active' => true]);
        $role->permissions()->attach(Permission::whereIn('code', ['shops.switch', 'godowns.switch'])->pluck('id'));
        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Godown Context Operator',
            'username' => 'godown-context-operator',
            'email' => 'godown-context-operator@example.test',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $primaryShop = Shop::create(['name' => 'Primary Linked Shop', 'code' => 'PRIMARY-LINK', 'is_active' => true]);
        $secondaryShop = Shop::create(['name' => 'Secondary Linked Shop', 'code' => 'SECONDARY-LINK', 'is_active' => true]);
        $unrelatedShop = Shop::create(['name' => 'Unrelated Assigned Shop', 'code' => 'UNRELATED', 'is_active' => true]);
        $godown = Godown::create(['shop_id' => $primaryShop->id, 'name' => 'Shared Godown', 'code' => 'SHARED-G', 'is_active' => true]);
        $godown->shops()->sync([$primaryShop->id, $secondaryShop->id]);
        $user->shops()->attach([
            $primaryShop->id => ['is_default' => true, 'is_active' => true],
            $secondaryShop->id => ['is_default' => false, 'is_active' => true],
            $unrelatedShop->id => ['is_default' => false, 'is_active' => true],
        ]);
        $user->godowns()->attach($godown, ['is_default' => true, 'is_active' => true]);

        $this->actingAs($user)
            ->getJson('/admin/location-context/shops?godown_id='.$godown->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $primaryShop->id, 'name' => $primaryShop->name, 'code' => $primaryShop->code])
            ->assertJsonFragment(['id' => $secondaryShop->id, 'name' => $secondaryShop->name, 'code' => $secondaryShop->code])
            ->assertJsonMissing(['id' => $unrelatedShop->id]);

        $this->withSession([
            'active_shop_id' => $unrelatedShop->id,
            'active_godown_id' => null,
        ])->actingAs($user)
            ->postJson('/admin/location-context/godown', ['godown_id' => $godown->id])
            ->assertOk()
            ->assertJsonPath('data.active_godown_id', $godown->id)
            ->assertJsonPath('data.active_shop_id', $primaryShop->id)
            ->assertJsonMissing(['id' => $unrelatedShop->id]);

        $this->assertSame($primaryShop->id, session('active_shop_id'));
        $this->assertSame($godown->id, session('active_godown_id'));

        $this->actingAs($user)
            ->postJson('/admin/location-context/shop', ['shop_id' => $unrelatedShop->id])
            ->assertForbidden();
    }

    public function test_context_switch_is_audited_without_logging_the_user_out(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $first = Shop::create(['name' => 'Alpha Context', 'code' => 'ALPHA-C', 'is_active' => true]);
        $second = Shop::create(['name' => 'Beta Context', 'code' => 'BETA-C', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->assertSame($first->id, session('active_shop_id'));

        $this->actingAs($admin)->postJson('/admin/location-context/shop', ['shop_id' => $second->id])
            ->assertOk();

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('context_switch_logs', [
            'user_id' => $admin->id,
            'from_shop_id' => $first->id,
            'to_shop_id' => $second->id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'event' => 'context.shop_switched']);
    }

    public function test_location_assignment_changes_are_available_on_the_next_request(): void
    {
        $role = Role::create(['name' => 'Location User', 'slug' => 'location-user', 'is_active' => true]);
        $role->permissions()->attach(Permission::where('code', 'dashboard.view')->firstOrFail());
        $user = User::create(['role_id' => $role->id, 'name' => 'Location User', 'username' => 'location', 'email' => 'location@example.test', 'password' => 'password123', 'is_active' => true]);
        $shop = Shop::create(['name' => 'New Assignment', 'code' => 'NEW', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
        $this->assertNull(session('active_shop_id'));

        $user->shops()->attach($shop);
        $this->actingAs($user->fresh())->get('/admin/dashboard')->assertOk()->assertSee('New Assignment');
        $this->assertSame($shop->id, session('active_shop_id'));
    }

    public function test_user_assignment_rejects_a_godown_from_an_unassigned_shop(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $role = Role::create(['name' => 'Assignment Role', 'slug' => 'assignment-role', 'is_active' => true]);
        $assignedShop = Shop::create(['name' => 'Assigned Shop', 'code' => 'ASSIGNED-S', 'is_active' => true]);
        $otherShop = Shop::create(['name' => 'Other Shop', 'code' => 'OTHER-S', 'is_active' => true]);
        $otherGodown = Godown::create(['shop_id' => $otherShop->id, 'name' => 'Other Godown', 'code' => 'OTHER-G', 'is_active' => true]);

        $this->actingAs($admin)->from('/admin/access/users')->post('/admin/access/users', [
            'name' => 'Invalid Assignment',
            'username' => 'invalid-assignment',
            'email' => 'invalid-assignment@example.test',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => 1,
            'shops' => [$assignedShop->id],
            'godowns' => [$otherGodown->id],
        ])->assertRedirect('/admin/access/users')->assertSessionHasErrors('godowns');

        $this->assertDatabaseMissing('users', ['username' => 'invalid-assignment']);
    }

    public function test_super_admin_can_monitor_database_sessions(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        DB::table('sessions')->insert([
            'id' => 'staff-session-id',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get('/admin/access/sessions')
            ->assertOk()
            ->assertJsonFragment(['user_name' => 'Super Admin', 'ip_address' => '127.0.0.1']);
    }
}
