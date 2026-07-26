<?php

namespace Tests\Feature;

use App\Models\ContactEnquiry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_open_dashboard_and_all_module_shells(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Module workspace blueprint')
            ->assertSee('Settings &amp; Configuration', false)
            ->assertSee('Sales Overview');

        foreach (array_keys(config('cholavin_dashboard.modules')) as $module) {
            if ($module === 'dashboard') {
                continue;
            }

            $this->actingAs($admin)->get('/admin/workspace/'.$module)->assertOk()->assertSee('AJAX ready');
        }
    }

    public function test_product_permission_can_open_live_product_backend_without_dashboard_permission(): void
    {
        $permission = Permission::where('code', 'products.view')->firstOrFail();
        $role = Role::create(['name' => 'Catalog Viewer', 'slug' => 'catalog-viewer', 'is_active' => true]);
        $role->permissions()->attach($permission);
        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Catalog Viewer',
            'username' => 'catalog-viewer',
            'email' => 'catalog-viewer@example.test',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/products')->assertOk();
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_enquiry_status_update_is_authenticated_and_persisted(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $enquiry = ContactEnquiry::create(['name' => 'Test Buyer', 'email' => 'buyer@example.test', 'message' => 'Need rice quotation']);

        $this->actingAs($admin)
            ->patchJson('/admin/enquiries/'.$enquiry->id.'/status', ['status' => 'contacted', 'reason' => 'Called and shared catalog'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_enquiries', ['id' => $enquiry->id, 'status' => 'contacted']);
    }
}
