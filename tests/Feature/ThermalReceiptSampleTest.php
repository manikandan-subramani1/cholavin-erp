<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThermalReceiptSampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
    }

    public function test_guest_cannot_open_thermal_receipt_sample(): void
    {
        $this->get('/admin/sales/thermal-receipt/sample')->assertRedirect('/admin/login');
    }

    public function test_authorized_user_can_preview_both_supported_paper_widths(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/sales/thermal-receipt/sample?paper_width=80')
            ->assertOk()
            ->assertSee('SAMPLE-0001')
            ->assertSee('Premium Ponni Rice 5 kg')
            ->assertSee('80 mm (recommended)');

        $this->actingAs($admin)
            ->get('/admin/sales/thermal-receipt/sample?paper_width=58')
            ->assertOk();
    }

    public function test_user_without_sales_permission_is_forbidden(): void
    {
        $role = Role::create(['name' => 'No Sales', 'slug' => 'no-sales', 'is_active' => true]);
        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Staff',
            'username' => 'receipt-staff',
            'email' => 'receipt-staff@example.test',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/sales/thermal-receipt/sample')->assertForbidden();
    }

    public function test_authorized_user_can_download_thermal_pdf(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/sales/thermal-receipt/sample/pdf?paper_width=80')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('sales-receipt-SAMPLE-0001.pdf');
    }
}
