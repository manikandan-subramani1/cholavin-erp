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
}
