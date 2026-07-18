<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResponsiveBusinessModalsTest extends TestCase
{
    public function test_business_forms_use_responsive_modals_instead_of_offcanvas_drawers(): void
    {
        $views = [
            'backend/access/locations.blade.php',
            'backend/access/roles.blade.php',
            'backend/access/users.blade.php',
            'backend/accounts/payments.blade.php',
            'backend/accounts/vouchers.blade.php',
            'backend/delivery/index.blade.php',
            'backend/inventory/transfers.blade.php',
            'backend/parties/index.blade.php',
            'backend/documents/index.blade.php',
            'backend/enquiries/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents(resource_path('views/'.$view));

            $this->assertStringContainsString('modal fade', $contents, $view);
            $this->assertStringContainsString('modal-dialog-scrollable', $contents, $view);
            $this->assertStringContainsString('modal-fullscreen-sm-down', $contents, $view);
            $this->assertStringNotContainsString('offcanvas', $contents, $view);
        }
    }

    public function test_business_form_scripts_control_bootstrap_modals(): void
    {
        $scripts = [
            'access-locations.js',
            'access-roles.js',
            'access-users.js',
            'payments.js',
            'vouchers.js',
            'deliveries.js',
            'stock-transfers.js',
        ];

        foreach ($scripts as $script) {
            $contents = file_get_contents(public_path('backend/assets/js/modules/'.$script));

            $this->assertStringContainsString('bootstrap.Modal', $contents, $script);
            $this->assertStringNotContainsString('bootstrap.Offcanvas', $contents, $script);
            $this->assertStringNotContainsString('-drawer', $contents, $script);
        }
    }

    public function test_form_wrappers_preserve_bootstrap_modal_body_scrolling(): void
    {
        $css = file_get_contents(public_path('backend/assets/css/cholavin-erp.css'));

        $this->assertStringContainsString('.modal-dialog-scrollable .modal-content > form', $css);
        $this->assertStringContainsString('min-height: 0;', $css);
        $this->assertStringContainsString('overflow-y: auto;', $css);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $css);
        $this->assertStringContainsString('.modal-fullscreen-sm-down .modal-content > form', $css);
    }
}
