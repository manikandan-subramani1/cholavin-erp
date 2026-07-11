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

        $response = $this->post('/admin/login', ['login' => 'superadmin', 'password' => 'ChangeMe@123']);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, session('user_id'));
        $this->assertSame($user->role_id, session('role_id'));
        $this->assertSame(['*'], session('permitted_shop_ids'));
        $this->assertSame(['*'], session('permitted_actions'));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'login.success']);
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
            '/admin/settings',
            '/admin/dashboard',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_super_admin_can_switch_shop_and_godown_context_without_logout(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $shop = Shop::create(['name' => 'Context Shop', 'code' => 'CTX', 'is_active' => true]);
        $godown = Godown::create(['shop_id' => $shop->id, 'name' => 'Context Godown', 'code' => 'CTX-G', 'is_active' => true]);

        $this->actingAs($admin)->post('/admin/location-context', [
            'shop_id' => $shop->id,
            'godown_id' => $godown->id,
        ])->assertRedirect();

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($shop->id, session('active_shop_id'));
        $this->assertSame($godown->id, session('active_godown_id'));
    }

    public function test_location_assignment_changes_are_available_on_the_next_request(): void
    {
        $role = Role::create(['name' => 'Location User', 'slug' => 'location-user', 'is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Location User', 'username' => 'location', 'email' => 'location@example.test', 'password' => 'password123', 'is_active' => true]);
        $shop = Shop::create(['name' => 'New Assignment', 'code' => 'NEW', 'is_active' => true]);

        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
        $this->assertNull(session('active_shop_id'));

        $user->shops()->attach($shop);
        $this->actingAs($user->fresh())->get('/admin/dashboard')->assertOk()->assertSee('New Assignment');
        $this->assertSame($shop->id, session('active_shop_id'));
    }
}
