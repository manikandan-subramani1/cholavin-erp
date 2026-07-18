<?php

namespace Tests\Feature;

use Tests\TestCase;

class BackendBrandingTest extends TestCase
{
    public function test_backend_uses_the_frontend_favicon_for_browser_and_compact_branding(): void
    {
        $favicon = "frontend/assets/img/logo/favicon.png";
        $layout = file_get_contents(resource_path('views/backend/layouts/app.blade.php'));
        $menu = file_get_contents(resource_path('views/backend/layouts/menu.blade.php'));
        $header = file_get_contents(resource_path('views/backend/layouts/header.blade.php'));

        $this->assertStringContainsString($favicon, $layout);
        $this->assertStringNotContainsString('backend/assets/images/favicon.ico', $layout);
        $this->assertSame(2, substr_count($menu, $favicon));
        $this->assertSame(2, substr_count($header, $favicon));
        $this->assertStringNotContainsString('ri-seedling-fill', $menu.$header);

        foreach (['login', 'forgot-password', 'reset-password'] as $view) {
            $contents = file_get_contents(resource_path('views/backend/auth/'.$view.'.blade.php'));
            $this->assertStringContainsString($favicon, $contents, $view);
        }
    }

    public function test_compact_sidebar_flyout_uses_the_cholavin_maroon_palette(): void
    {
        $css = file_get_contents(public_path('backend/assets/css/cholavin-erp.css'));

        $this->assertStringContainsString('--vz-vertical-menu-bg: var(--cholavin-deep)', $css);
        $this->assertStringContainsString('--vz-vertical-menu-sub-item-active-color: var(--cholavin-gold)', $css);
        $this->assertStringContainsString('linear-gradient(180deg, var(--cholavin-maroon), var(--cholavin-deep))', $css);
    }
}
