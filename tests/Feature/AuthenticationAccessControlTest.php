<?php

namespace Tests\Feature;

use App\Models\Godown;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

    public function test_user_can_login_with_username_and_permission_context_is_loaded(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->post('/admin/login', ['login' => 'admin@gmail.com', 'password' => '12345678']);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, session('user_id'));
        $this->assertSame($user->role_id, session('role_id'));
        $this->assertSame(['*'], session('permitted_shop_ids'));
        $this->assertSame(['*'], session('permitted_actions'));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'login.success']);
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'event' => 'login.success']);
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
            ->assertSee('id="customizer-layout01"', false)
            ->assertSee('value="vertical"', false)
            ->assertSee('id="customizer-layout02"', false)
            ->assertSee('value="horizontal"', false)
            ->assertSeeInOrder([
                '<span>Home</span>',
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
            ->assertSee('id="page-header-cart-dropdown"', false)
            ->assertSee('data-toggle="fullscreen"', false)
            ->assertSee('light-dark-mode', false)
            ->assertSee('id="page-header-notifications-dropdown"', false)
            ->assertSee('id="page-header-user-dropdown"', false)
            ->assertSee('id="removeNotificationModal"', false)
            ->assertSee('id="delete-notification"', false);
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
