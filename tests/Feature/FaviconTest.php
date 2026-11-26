<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaviconTest extends TestCase
{
    public function test_halaman_login_memuat_tautan_favicon(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('favicon.ico?v=3', false);
        $response->assertSee('favicon-32x32.png?v=3', false);
        $response->assertSee('favicon-16x16.png?v=3', false);
        $response->assertSee('apple-touch-icon.png?v=3', false);
    }

    public function test_tautan_favicon_mengarah_ke_file_yang_ada_di_public(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();

        preg_match_all('/<link[^>]+rel="(?:icon|apple-touch-icon)"[^>]+href="([^"]+)"/i', $response->getContent(), $matches);
        $this->assertCount(4, $matches[1]);

        foreach ($matches[1] as $href) {
            $path = trim((string) parse_url($href, PHP_URL_PATH), '/');
            $this->assertFileExists(public_path($path));
        }
    }

    public function test_tidak_ada_style_atau_script_inline_di_layout(): void
    {
        $layouts = [
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/guru.blade.php'),
            resource_path('views/layouts/admin.blade.php'),
            resource_path('views/layouts/siswa.blade.php'),
            resource_path('views/partials/favicon.blade.php'),
        ];

        foreach ($layouts as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $isi = file_get_contents($file);
            $this->assertIsString($isi);
            $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $isi);
            $this->assertStringNotContainsString('<style', $isi);
            $this->assertStringNotContainsString('style=', $isi);
            $this->assertStringNotContainsString('onclick=', $isi);
        }
    }
}
