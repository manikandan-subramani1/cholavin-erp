<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicationJavascriptStyleTest extends TestCase
{
    public function test_application_scripts_use_jquery_instead_of_mixed_native_dom_and_fetch_calls(): void
    {
        $excludedRuntimeFiles = ['app.js', 'layout.js', 'plugins.js'];
        $scripts = collect(File::allFiles(public_path('backend/assets/js')))
            ->filter(function ($file) use ($excludedRuntimeFiles) {
                $path = str_replace('\\', '/', $file->getRelativePathname());

                return $file->getExtension() === 'js'
                    && ! str_ends_with($path, '.min.js')
                    && ! preg_match('#(^|/)(vendor|libs|pages)/#', $path)
                    && ! in_array($file->getFilename(), $excludedRuntimeFiles, true);
            });

        $this->assertNotEmpty($scripts);

        foreach ($scripts as $script) {
            $contents = File::get($script->getPathname());
            $name = str_replace('\\', '/', $script->getRelativePathname());

            $this->assertStringContainsString('window.jQuery', $contents, $name.' must receive jQuery explicitly.');
            $this->assertDoesNotMatchRegularExpression('/\bfetch\s*\(/', $contents, $name);
            $this->assertDoesNotMatchRegularExpression('/new\s+XMLHttpRequest\b/', $contents, $name);
            $this->assertDoesNotMatchRegularExpression('/\.addEventListener\s*\(/', $contents, $name);
            $this->assertDoesNotMatchRegularExpression('/\.(?:querySelector|querySelectorAll|getElementById)\s*\(/', $contents, $name);
        }
    }

    public function test_networked_application_features_use_jquery_ajax_services(): void
    {
        $search = File::get(public_path('backend/assets/js/header-search.js'));
        $navigation = File::get(public_path('backend/assets/js/erp-navigation.js'));

        $this->assertStringContainsString('CholavinAjax.request', $search);
        $this->assertStringContainsString('$.ajax', $navigation);
    }

    public function test_party_workspace_uses_shell_drawer_and_ajax_navigation_helpers(): void
    {
        $script = File::get(public_path('backend/assets/js/modules/parties.js'));

        $this->assertStringContainsString('openPartyDrawer', $script);
        $this->assertStringContainsString('window.CholavinShell.openDrawer', $script);
        $this->assertStringContainsString('window.CholavinShell.setQuickActions', $script);
        $this->assertStringContainsString('window.CholavinNavigation', $script);
        $this->assertStringContainsString('data-party-action="view"', $script);
    }

    public function test_product_workspace_uses_shell_drawer_and_quick_actions(): void
    {
        $script = File::get(public_path('backend/assets/js/modules/products-index.js'));

        $this->assertStringContainsString('openProductDrawer', $script);
        $this->assertStringContainsString('window.CholavinShell.openDrawer', $script);
        $this->assertStringContainsString('window.CholavinShell.setQuickActions', $script);
        $this->assertStringContainsString('data-product-action="view"', $script);
        $this->assertStringContainsString('window.CholavinNavigation', $script);
    }


    public function test_document_workspace_uses_fast_shortcuts_and_shell_actions(): void
    {
        $script = File::get(public_path('backend/assets/js/modules/commercial-documents.js'));

        $this->assertStringContainsString('installQuickActions', $script);
        $this->assertStringContainsString('window.CholavinShell.setQuickActions', $script);
        $this->assertStringContainsString('data-document-shortcut', $script);
        $this->assertStringContainsString('save-print', $script);
        $this->assertStringContainsString('save-pay', $script);
        $this->assertStringContainsString('idempotency_key', $script);
    }

}
